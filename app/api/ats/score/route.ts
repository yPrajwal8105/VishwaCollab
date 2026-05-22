import { NextRequest, NextResponse } from 'next/server';
import { createClient } from '@/lib/supabase/server';
import { calculateATSScore } from '@/lib/ats-scorer';
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
    const { resumeId, jobDescription, role } = body;

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

    // Calculate ATS score with rate limiting
    const scoreResult = await withRateLimit(
      `ats-${user.id}`,
      async () => {
        return await withRetry(
          () =>
            calculateATSScore(
              {
                rawText: parsedData.raw_text,
                workExperience: parsedData.work_experience as any,
                skills: parsedData.skills || [],
                education: parsedData.education as any,
                projects: parsedData.projects as any,
              },
              jobDescription,
              role
            ),
          { maxRetries: 2 }
        );
      }
    );

    // Save ATS score
    const { data: atsData, error: atsError } = await supabase
      .from('ats_scores')
      .insert({
        user_id: user.id,
        resume_id: resumeId,
        job_description: jobDescription || null,
        role: role || null,
        overall_score: scoreResult.overallScore,
        keyword_match_percentage: scoreResult.keywordMatchPercentage,
        missing_skills: scoreResult.missingSkills,
        suggestions: scoreResult.suggestions,
      })
      .select()
      .single();

    if (atsError) {
      console.error('ATS score save error:', atsError);
    }

    logAPIUsage('/api/ats/score');

    return NextResponse.json({
      success: true,
      score: scoreResult,
      atsRecord: atsData,
    });
  } catch (error) {
    console.error('ATS scoring error:', error);
    return NextResponse.json(
      { error: 'Internal server error' },
      { status: 500 }
    );
  }
}

