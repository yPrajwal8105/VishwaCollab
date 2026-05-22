import { NextRequest, NextResponse } from 'next/server';
import { createClient } from '@/lib/supabase/server';
import { calculateScore } from '@/lib/utils';

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
    const { role, answers, timeTaken } = body;

    if (!role || !answers || typeof timeTaken !== 'number') {
      return NextResponse.json(
        { error: 'Missing required fields' },
        { status: 400 }
      );
    }

    // Fetch quiz questions to validate answers
    const { data: questions, error: questionsError } = await supabase
      .from('quiz_questions')
      .select('*')
      .eq('role', role)
      .order('created_at', { ascending: false })
      .limit(10);

    if (questionsError || !questions || questions.length === 0) {
      return NextResponse.json(
        { error: 'Quiz questions not found' },
        { status: 404 }
      );
    }

    // Calculate score
    let correctAnswers = 0;
    const answerDetails: Array<{
      questionId: string;
      selectedAnswer: number;
      correctAnswer: number;
      isCorrect: boolean;
    }> = [];

    questions.forEach((q, index) => {
      const selectedAnswer = answers[index];
      const isCorrect = selectedAnswer === q.correct_answer;
      if (isCorrect) correctAnswers++;

      answerDetails.push({
        questionId: q.id,
        selectedAnswer,
        correctAnswer: q.correct_answer,
        isCorrect,
      });
    });

    const score = calculateScore(correctAnswers, questions.length);

    // Save quiz result
    const { data: quizResult, error: resultError } = await supabase
      .from('quiz_results')
      .insert({
        user_id: user.id,
        role,
        score,
        total_questions: questions.length,
        correct_answers: correctAnswers,
        time_taken: timeTaken,
        answers: answerDetails,
      })
      .select()
      .single();

    if (resultError) {
      console.error('Quiz result save error:', resultError);
      return NextResponse.json(
        { error: 'Failed to save quiz result' },
        { status: 500 }
      );
    }

    // Update leaderboard
    const { error: leaderboardError } = await supabase
      .from('leaderboard')
      .insert({
        user_id: user.id,
        role,
        score,
        quiz_result_id: quizResult.id,
      });

    if (leaderboardError) {
      console.error('Leaderboard update error:', leaderboardError);
    }

    return NextResponse.json({
      success: true,
      result: {
        score,
        correctAnswers,
        totalQuestions: questions.length,
        timeTaken,
        answerDetails,
      },
    });
  } catch (error) {
    console.error('Quiz submit error:', error);
    return NextResponse.json(
      { error: 'Internal server error' },
      { status: 500 }
    );
  }
}

