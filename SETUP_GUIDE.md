# Setup Guide for VishwaCollab

This guide will help you set up the complete VishwaCollab application from scratch.

## Prerequisites

1. **Node.js 18+** - [Download](https://nodejs.org/)
2. **Supabase Account** - [Sign up](https://supabase.com/)
3. **OpenAI API Key** - [Get API Key](https://platform.openai.com/api-keys)
4. **Adzuna API Credentials** (Optional) - [Sign up](https://developer.adzuna.com/)

## Step 1: Clone and Install

```bash
# Navigate to your project directory
cd Vishwacollab

# Install dependencies
npm install
```

## Step 2: Set Up Supabase

### 2.1 Create Supabase Project

1. Go to [Supabase Dashboard](https://app.supabase.com/)
2. Click "New Project"
3. Fill in project details:
   - Name: `vishwacollab` (or your preferred name)
   - Database Password: (save this securely)
   - Region: Choose closest to you
4. Wait for project to be created (2-3 minutes)

### 2.2 Run Database Migration

1. In Supabase Dashboard, go to **SQL Editor**
2. Click "New Query"
3. Copy the entire content of `supabase/migrations/001_initial_schema.sql`
4. Paste into the SQL Editor
5. Click "Run" (or press Ctrl+Enter)
6. Verify all tables are created (check Table Editor)

### 2.3 Create Storage Bucket

1. In Supabase Dashboard, go to **Storage**
2. Click "New Bucket"
3. Name: `resumes`
4. Make it **Public** (or configure RLS policies)
5. Click "Create bucket"

### 2.4 Get API Keys

1. Go to **Settings** → **API**
2. Copy:
   - **Project URL** → `NEXT_PUBLIC_SUPABASE_URL`
   - **anon public** key → `NEXT_PUBLIC_SUPABASE_ANON_KEY`
   - **service_role** key → `SUPABASE_SERVICE_ROLE_KEY` (keep this secret!)

## Step 3: Configure Environment Variables

1. Create `.env.local` file in the root directory:

```env
# Supabase Configuration
NEXT_PUBLIC_SUPABASE_URL=https://your-project.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=your-anon-key-here
SUPABASE_SERVICE_ROLE_KEY=your-service-role-key-here

# OpenAI Configuration
OPENAI_API_KEY=sk-your-openai-api-key-here

# Adzuna Jobs API Configuration (Optional)
ADZUNA_APP_ID=your-adzuna-app-id
ADZUNA_APP_KEY=your-adzuna-app-key

# Application Configuration
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

2. Replace all placeholder values with your actual credentials

## Step 4: Get API Keys

### OpenAI API Key

1. Go to [OpenAI Platform](https://platform.openai.com/)
2. Sign in or create account
3. Go to **API Keys** section
4. Click "Create new secret key"
5. Copy the key (you won't see it again!)
6. Add to `.env.local` as `OPENAI_API_KEY`

**Note**: You'll need to add billing information to use OpenAI API.

### Adzuna API (Optional)

1. Go to [Adzuna Developer Portal](https://developer.adzuna.com/)
2. Sign up for free account
3. Create a new application
4. Get your App ID and App Key
5. Add to `.env.local`

## Step 5: Run the Application

```bash
# Start development server
npm run dev
```

Open [http://localhost:3000](http://localhost:3000) in your browser.

## Step 6: Test the Application

1. **Sign Up**: Create a new account
2. **Upload Resume**: Upload a PDF or DOCX resume
3. **Get ATS Score**: Calculate your resume's ATS score
4. **Take Quiz**: Generate and take a skill-based quiz
5. **View Jobs**: Get job recommendations
6. **Check Leaderboard**: See top performers

## Troubleshooting

### Database Connection Issues

- Verify Supabase project is active
- Check API keys are correct
- Ensure migration was run successfully

### Resume Upload Fails

- Check Supabase Storage bucket `resumes` exists
- Verify bucket is public or RLS policies allow uploads
- Check file size (max 10MB)

### OpenAI API Errors

- Verify API key is correct
- Check you have credits/billing set up
- Review rate limits in OpenAI dashboard

### Adzuna API Errors

- Verify App ID and App Key
- Check API quota/limits
- Some regions may have limited job data

## Production Deployment

### Vercel (Recommended)

1. Push code to GitHub
2. Import project in Vercel
3. Add environment variables in Vercel dashboard
4. Deploy!

### Other Platforms

1. Build the app: `npm run build`
2. Set environment variables in your platform
3. Deploy the `.next` folder

## Security Notes

- **Never commit `.env.local`** to version control
- Keep `SUPABASE_SERVICE_ROLE_KEY` secret (server-side only)
- Use RLS policies in Supabase for data security
- Regularly rotate API keys

## Next Steps

- Customize UI/UX to match your brand
- Add more quiz questions
- Integrate additional job boards
- Add email notifications
- Implement advanced analytics

## Support

If you encounter issues:
1. Check the error message in browser console
2. Review Supabase logs
3. Check API quotas/limits
4. Verify all environment variables are set

Happy coding! 🚀

