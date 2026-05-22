import { NextRequest, NextResponse } from 'next/server';
import { createClient } from '@/lib/supabase/server';
import { identifyTopRoles, generateQuizForRole } from '@/lib/quiz-generator';
import { withRetry, withRateLimit, logAPIUsage } from '@/lib/api-utils';

export async function POST(request: NextRequest) {
  try {
    const supabase = await createClient();
    const {
      data: { user },
    } = await supabase.auth.getUser();

    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const body = await request.json();
    const { resumeId, role } = body;

    if (!resumeId) {
      return NextResponse.json(
        { error: 'Resume ID is required' },
        { status: 400 }
      );
    }

    // Fetch parsed resume data
    const { data: parsedData, error: parsedError } = await supabase
      .from('parsed_resume_data')
      .select('*')
      .eq('resume_id', resumeId)
      .eq('user_id', user.id)
      .single();

    if (parsedError || !parsedData) {
      return NextResponse.json(
        { error: 'Resume not found or not parsed' },
        { status: 404 }
      );
    }

    const skills = parsedData.skills || [];

    // Identify top roles if not provided
    const targetRole = role || identifyTopRoles(skills)[0];

    // Generate quiz with rate limiting
    const questions = await withRateLimit(
      `quiz-${user.id}`,
      async () => {
        return await withRetry(
          () => generateQuizForRole(targetRole, skills, true),
          { maxRetries: 2 }
        );
      }
    );

    // Save quiz questions to database
    const quizQuestions = questions.map(q => ({
      role: targetRole,
      question: q.question,
      options: q.options,
      correct_answer: q.correctAnswer,
      difficulty: q.difficulty,
    }));

    const { error: saveError } = await supabase
      .from('quiz_questions')
      .insert(quizQuestions);

    if (saveError) {
      console.error('Quiz save error:', saveError);
    }

    logAPIUsage('/api/quiz/generate');

    return NextResponse.json({
      success: true,
      role: targetRole,
      questions,
      topRoles: identifyTopRoles(skills),
    });
  } catch (error) {
    console.error('Quiz generation error:', error);
    return NextResponse.json(
      { error: 'Internal server error' },
      { status: 500 }
    );
  }
}

