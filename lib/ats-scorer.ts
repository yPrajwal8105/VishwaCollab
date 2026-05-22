import OpenAI from 'openai';
import { ParsedResume } from './resume-parser';

const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

export interface ATSScoreResult {
  overallScore: number;
  keywordMatchPercentage: number;
  missingSkills: string[];
  suggestions: Array<{
    category: string;
    suggestion: string;
    priority: 'high' | 'medium' | 'low';
  }>;
  matchedKeywords: string[];
  missingKeywords: string[];
}

export async function calculateATSScore(
  resume: ParsedResume,
  jobDescription?: string,
  role?: string
): Promise<ATSScoreResult> {
  try {
    // Extract keywords from job description or role
    const targetKeywords = jobDescription
      ? extractKeywordsFromJobDescription(jobDescription)
      : role
      ? getKeywordsForRole(role)
      : [];

    // Calculate keyword match
    const resumeText = resume.rawText.toLowerCase();
    const matchedKeywords: string[] = [];
    const missingKeywords: string[] = [];

    targetKeywords.forEach(keyword => {
      if (resumeText.includes(keyword.toLowerCase())) {
        matchedKeywords.push(keyword);
      } else {
        missingKeywords.push(keyword);
      }
    });

    const keywordMatchPercentage =
      targetKeywords.length > 0
        ? (matchedKeywords.length / targetKeywords.length) * 100
        : 0;

    // Use OpenAI to generate comprehensive ATS score and suggestions
    const aiAnalysis = await getAIAnalysis(resume, jobDescription, role, targetKeywords, missingKeywords);

    // Calculate overall score (weighted average)
    const overallScore = Math.round(
      keywordMatchPercentage * 0.4 +
        aiAnalysis.contentScore * 0.3 +
        aiAnalysis.formatScore * 0.2 +
        aiAnalysis.relevanceScore * 0.1
    );

    return {
      overallScore: Math.min(100, Math.max(0, overallScore)),
      keywordMatchPercentage: Math.round(keywordMatchPercentage * 100) / 100,
      missingSkills: missingKeywords,
      suggestions: aiAnalysis.suggestions,
      matchedKeywords,
      missingKeywords,
    };
  } catch (error) {
    console.error('Error calculating ATS score:', error);
    // Fallback calculation
    return calculateFallbackScore(resume, jobDescription, role);
  }
}

async function getAIAnalysis(
  resume: ParsedResume,
  jobDescription: string | undefined,
  role: string | undefined,
  targetKeywords: string[],
  missingKeywords: string[]
): Promise<{
  contentScore: number;
  formatScore: number;
  relevanceScore: number;
  suggestions: Array<{
    category: string;
    suggestion: string;
    priority: 'high' | 'medium' | 'low';
  }>;
}> {
  try {
    const prompt = `Analyze this resume and provide an ATS (Applicant Tracking System) score analysis.

Resume Summary:
- Skills: ${resume.skills.join(', ')}
- Work Experience: ${resume.workExperience.length} positions
- Education: ${resume.education.length} entries
- Projects: ${resume.projects.length} projects

${jobDescription ? `Job Description: ${jobDescription.substring(0, 500)}` : ''}
${role ? `Target Role: ${role}` : ''}
${targetKeywords.length > 0 ? `Required Keywords: ${targetKeywords.join(', ')}` : ''}
${missingKeywords.length > 0 ? `Missing Keywords: ${missingKeywords.join(', ')}` : ''}

Provide a JSON response with:
{
  "contentScore": number (0-100),
  "formatScore": number (0-100),
  "relevanceScore": number (0-100),
  "suggestions": [
    {
      "category": string,
      "suggestion": string,
      "priority": "high" | "medium" | "low"
    }
  ]
}`;

    const response = await openai.chat.completions.create({
      model: 'gpt-4-turbo-preview',
      messages: [
        {
          role: 'system',
          content: 'You are an expert ATS (Applicant Tracking System) analyzer. Provide detailed, actionable feedback.',
        },
        {
          role: 'user',
          content: prompt,
        },
      ],
      response_format: { type: 'json_object' },
      temperature: 0.3,
      max_tokens: 1000,
    });

    const content = response.choices[0]?.message?.content;
    if (content) {
      const analysis = JSON.parse(content);
      return {
        contentScore: analysis.contentScore || 70,
        formatScore: analysis.formatScore || 75,
        relevanceScore: analysis.relevanceScore || 70,
        suggestions: analysis.suggestions || [],
      };
    }
  } catch (error) {
    console.error('OpenAI API error:', error);
  }

  // Fallback scores
  return {
    contentScore: 70,
    formatScore: 75,
    relevanceScore: 70,
    suggestions: [],
  };
}

function extractKeywordsFromJobDescription(jobDescription: string): string[] {
  const keywords: string[] = [];
  const lowerDesc = jobDescription.toLowerCase();

  // Common technical skills
  const techSkills = [
    'javascript', 'typescript', 'python', 'java', 'react', 'node.js',
    'sql', 'mongodb', 'aws', 'docker', 'kubernetes', 'git', 'agile',
    'machine learning', 'data science', 'api', 'rest', 'graphql'
  ];

  techSkills.forEach(skill => {
    if (lowerDesc.includes(skill)) {
      keywords.push(skill);
    }
  });

  // Extract capitalized words (likely technologies/terms)
  const capitalizedWords = jobDescription.match(/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\b/g);
  if (capitalizedWords) {
    keywords.push(...capitalizedWords.map(w => w.trim()));
  }

  return [...new Set(keywords)];
}

function getKeywordsForRole(role: string): string[] {
  const roleKeywords: Record<string, string[]> = {
    'sde': ['programming', 'algorithms', 'data structures', 'software development', 'coding'],
    'frontend': ['react', 'javascript', 'html', 'css', 'ui/ux', 'responsive design'],
    'backend': ['node.js', 'api', 'database', 'server', 'rest', 'graphql'],
    'data analyst': ['sql', 'python', 'data analysis', 'statistics', 'excel', 'visualization'],
    'full stack': ['react', 'node.js', 'database', 'api', 'javascript', 'typescript'],
  };

  const lowerRole = role.toLowerCase();
  for (const [key, keywords] of Object.entries(roleKeywords)) {
    if (lowerRole.includes(key)) {
      return keywords;
    }
  }

  return [];
}

function calculateFallbackScore(
  resume: ParsedResume,
  jobDescription?: string,
  role?: string
): ATSScoreResult {
  // Simple fallback scoring
  let score = 50;

  if (resume.skills.length > 5) score += 10;
  if (resume.workExperience.length > 0) score += 15;
  if (resume.education.length > 0) score += 10;
  if (resume.projects.length > 0) score += 10;
  if (resume.rawText.length > 500) score += 5;

  return {
    overallScore: Math.min(100, score),
    keywordMatchPercentage: 60,
    missingSkills: [],
    suggestions: [
      {
        category: 'General',
        suggestion: 'Consider adding more specific technical skills and quantifiable achievements.',
        priority: 'medium',
      },
    ],
    matchedKeywords: [],
    missingKeywords: [],
  };
}

