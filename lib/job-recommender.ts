import axios from 'axios';

export interface Job {
  id: string;
  title: string;
  company: string;
  location: string;
  description: string;
  salary_min?: number;
  salary_max?: number;
  salary_currency?: string;
  url: string;
  created: string;
}

export interface JobRecommendationResult {
  jobs: Job[];
  totalResults: number;
  cached: boolean;
}

const ADZUNA_API_BASE = 'https://api.adzuna.com/v1/api/jobs';

// Cache duration: 1 hour
const CACHE_DURATION_MS = 60 * 60 * 1000;

export async function getJobRecommendations(
  skills: string[],
  location: string = 'us',
  page: number = 1,
  resultsPerPage: number = 10
): Promise<JobRecommendationResult> {
  try {
    const appId = process.env.ADZUNA_APP_ID;
    const appKey = process.env.ADZUNA_APP_KEY;

    if (!appId || !appKey) {
      throw new Error('Adzuna API credentials not configured');
    }

    // Build search query from skills
    const query = skills.slice(0, 3).join(' ');

    const params = {
      app_id: appId,
      app_key: appKey,
      what: query,
      where: location,
      sort_by: 'date',
      results_per_page: resultsPerPage,
      page: page,
      content_type: 'json',
    };

    const response = await axios.get(`${ADZUNA_API_BASE}/${location}/search/${page}`, {
      params,
      timeout: 10000,
    });

    const data = response.data;

    const jobs: Job[] = (data.results || []).map((job: any) => ({
      id: job.id?.toString() || Math.random().toString(),
      title: job.title || 'Untitled Position',
      company: job.company?.display_name || 'Unknown Company',
      location: job.location?.display_name || location,
      description: job.description || '',
      salary_min: job.salary_min,
      salary_max: job.salary_max,
      salary_currency: job.salary_currency || 'USD',
      url: job.redirect_url || job.url || '#',
      created: job.created || new Date().toISOString(),
    }));

    return {
      jobs,
      totalResults: data.count || 0,
      cached: false,
    };
  } catch (error) {
    console.error('Error fetching job recommendations:', error);
    
    // Return empty result on error
    return {
      jobs: [],
      totalResults: 0,
      cached: false,
    };
  }
}

export function shouldUseCache(cachedAt: string, expiresAt: string): boolean {
  const now = new Date();
  const expires = new Date(expiresAt);
  return now < expires;
}

export function getCacheExpiry(): Date {
  const now = new Date();
  return new Date(now.getTime() + CACHE_DURATION_MS);
}

