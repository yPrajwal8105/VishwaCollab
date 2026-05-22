# VishwaCollab - Professional Career Platform

A complete Next.js 15 application for resume analysis, ATS scoring, skill quizzes, and job recommendations.

## Features

### Core Features

1. **Resume Upload + Parsing**
   - Accept PDF or DOCX files
   - Extract text cleanly
   - Parse work experience, skills, education, projects
   - Store raw and parsed data in Supabase
   - Generate embeddings for skill matching

2. **ATS Score Engine**
   - Score resume against job description or role
   - Generate overall ATS score (0-100)
   - Identify missing skills
   - Calculate keyword match percentage
   - Provide resume improvement suggestions

3. **Auto-Generated Quiz Based on Resume**
   - Identify top 3 roles based on skills
   - Generate MCQ quiz (10 questions per role)
   - Difficulty levels: easy → medium → hard
   - Track correct answers, time taken, and final score
   - Save scores in database

4. **Leaderboard System**
   - Global leaderboard
   - Role-based leaderboard
   - Show top 10 for each category
   - User rankings

5. **Job Recommendation Engine**
   - Integrate Adzuna Jobs API
   - Recommend jobs based on skills from resume
   - Support preferred location
   - Sort by date (most recent first)
   - Response caching for performance

6. **API Rate Limits & Reliability**
   - Retry logic with exponential backoff
   - Response caching
   - Throttling to avoid API bans
   - Fallback models if OpenAI quota reached
   - Token usage and rate limit logging

## Tech Stack

- **Frontend**: Next.js 15, TypeScript, TailwindCSS, ShadCN components
- **Backend**: Supabase (Auth, Database, Storage), Edge Functions
- **APIs**: OpenAI (GPT-4), Adzuna Jobs API
- **Database**: PostgreSQL (via Supabase)
- **Storage**: Supabase Storage for resume files

## Setup Instructions

### 1. Prerequisites

- Node.js 18+ and npm/yarn
- Supabase account
- OpenAI API key
- Adzuna API credentials (optional, for job recommendations)

### 2. Install Dependencies

```bash
npm install
```

### 3. Environment Variables

Create a `.env.local` file in the root directory:

```env
# Supabase Configuration
NEXT_PUBLIC_SUPABASE_URL=your_supabase_project_url
NEXT_PUBLIC_SUPABASE_ANON_KEY=your_supabase_anon_key
SUPABASE_SERVICE_ROLE_KEY=your_supabase_service_role_key

# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key

# Adzuna Jobs API Configuration
ADZUNA_APP_ID=your_adzuna_app_id
ADZUNA_APP_KEY=your_adzuna_app_key

# Application Configuration
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

### 4. Supabase Setup

1. Create a new Supabase project
2. Run the migration file to create tables:
   - Go to SQL Editor in Supabase dashboard
   - Run `supabase/migrations/001_initial_schema.sql`

3. Create a storage bucket for resumes:
   - Go to Storage in Supabase dashboard
   - Create a bucket named `resumes`
   - Set it to public or configure RLS policies

4. Configure RLS policies (already in migration file)

### 5. Run Development Server

```bash
npm run dev
```

Open [http://localhost:3000](http://localhost:3000) in your browser.

## Project Structure

```
├── app/                    # Next.js app directory
│   ├── api/               # API routes
│   │   ├── resume/        # Resume upload endpoint
│   │   ├── ats/          # ATS scoring endpoint
│   │   ├── quiz/         # Quiz generation and submission
│   │   ├── jobs/         # Job recommendations
│   │   └── leaderboard/  # Leaderboard data
│   ├── dashboard/        # Dashboard pages
│   ├── login/            # Login page
│   ├── signup/          # Signup page
│   └── page.tsx          # Home page
├── components/           # React components
│   ├── ui/              # ShadCN UI components
│   ├── ResumeUpload.tsx
│   ├── ATSScoreDisplay.tsx
│   ├── QuizCard.tsx
│   ├── JobRecommendations.tsx
│   └── LeaderboardPreview.tsx
├── lib/                  # Utility libraries
│   ├── supabase/        # Supabase client setup
│   ├── resume-parser.ts # Resume parsing logic
│   ├── ats-scorer.ts    # ATS scoring engine
│   ├── quiz-generator.ts # Quiz generation
│   ├── job-recommender.ts # Job recommendations
│   └── api-utils.ts     # API utilities (retry, rate limit, cache)
├── supabase/            # Supabase migrations
│   └── migrations/
└── types/               # TypeScript types
    └── supabase.ts      # Supabase database types
```

## Database Schema

### Tables

- `users` - User profiles
- `resumes` - Uploaded resume files
- `parsed_resume_data` - Parsed resume information
- `quiz_questions` - Generated quiz questions
- `quiz_results` - Quiz attempt results
- `leaderboard` - Leaderboard entries
- `job_cache` - Cached job recommendations
- `ats_scores` - ATS scoring results

## API Endpoints

### Resume
- `POST /api/resume/upload` - Upload and parse resume

### ATS Scoring
- `POST /api/ats/score` - Calculate ATS score

### Quiz
- `POST /api/quiz/generate` - Generate quiz for role
- `POST /api/quiz/submit` - Submit quiz answers

### Jobs
- `POST /api/jobs/recommend` - Get job recommendations

### Leaderboard
- `GET /api/leaderboard` - Get leaderboard data

## Features in Detail

### Resume Parsing
- Supports PDF and DOCX formats
- Extracts: work experience, skills, education, projects
- Uses `pdf-parse` and `mammoth` libraries

### ATS Scoring
- Uses OpenAI GPT-4 for intelligent analysis
- Keyword matching algorithm
- Provides actionable suggestions
- Fallback scoring if API fails

### Quiz Generation
- AI-generated questions based on resume skills
- Role-specific questions
- Multiple difficulty levels
- Instant feedback

### Job Recommendations
- Integrates with Adzuna Jobs API
- Skill-based matching
- Location preferences
- Caching for performance

## Production Deployment

1. Build the application:
   ```bash
   npm run build
   ```

2. Deploy to Vercel or your preferred platform

3. Set environment variables in your deployment platform

4. Ensure Supabase project is configured for production

## License

MIT

## Support

For issues and questions, please open an issue on GitHub.

