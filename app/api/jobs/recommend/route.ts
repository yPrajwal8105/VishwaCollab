import { NextRequest, NextResponse } from 'next/server';
import { createClient } from '@/lib/supabase/server';
import {
  getJobRecommendations,
  shouldUseCache,
  getCacheExpiry,
} from '@/lib/job-recommender';
import { withRetry, withRateLimit, cache, logAPIUsage } from '@/lib/api-utils';

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
    const { resumeId, location = 'us', page = 1 } = body;

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

    // Check cache
    const cacheKey = `jobs-${user.id}-${location}-${page}`;
    const cachedResult = cache.get<{ jobs: any[]; totalResults: number }>(
      cacheKey
    );

    if (cachedResult) {
      // Check database cache
      const { data: dbCache } = await supabase
        .from('job_cache')
        .select('*')
        .eq('user_id', user.id)
        .eq('location', location)
        .gt('expires_at', new Date().toISOString())
        .single();

      if (dbCache && shouldUseCache(dbCache.cached_at, dbCache.expires_at)) {
        return NextResponse.json({
          success: true,
          jobs: dbCache.jobs,
          totalResults: (dbCache.jobs as any[]).length,
          cached: true,
        });
      }
    }

    // Fetch jobs with rate limiting
    const result = await withRateLimit(
      `jobs-${user.id}`,
      async () => {
        return await withRetry(
          () => getJobRecommendations(skills, location, page, 10),
          { maxRetries: 2 }
        );
      }
    );

    // Save to cache
    cache.set(cacheKey, result, 3600000); // 1 hour

    // Save to database cache
    const expiresAt = getCacheExpiry();
    await supabase.from('job_cache').insert({
      user_id: user.id,
      location,
      skills,
      jobs: result.jobs,
      expires_at: expiresAt.toISOString(),
    });

    logAPIUsage('/api/jobs/recommend');

    return NextResponse.json({
      success: true,
      ...result,
    });
  } catch (error) {
    console.error('Job recommendation error:', error);
    return NextResponse.json(
      { error: 'Internal server error' },
      { status: 500 }
    );
  }
}

