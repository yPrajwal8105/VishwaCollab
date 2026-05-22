import { redirect } from 'next/navigation';
import { createClient } from '@/lib/supabase/server';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import ResumeUpload from '@/components/ResumeUpload';
import ATSScoreDisplay from '@/components/ATSScoreDisplay';
import QuizCard from '@/components/QuizCard';
import JobRecommendations from '@/components/JobRecommendations';
import LeaderboardPreview from '@/components/LeaderboardPreview';

export default async function DashboardPage() {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  if (!user) {
    redirect('/login');
  }

  // Fetch user's latest resume
  const { data: latestResume } = await supabase
    .from('resumes')
    .select('*, parsed_resume_data(*), ats_scores(*)')
    .eq('user_id', user.id)
    .order('created_at', { ascending: false })
    .limit(1)
    .single();

  // Fetch latest ATS score
  const latestATSScore = latestResume?.ats_scores?.[0];

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="border-b bg-white">
        <div className="container mx-auto px-4 py-4 flex justify-between items-center">
          <h1 className="text-2xl font-bold text-indigo-600">VishwaCollab</h1>
          <div className="flex gap-4 items-center">
            <span className="text-sm text-gray-600">{user.email}</span>
            <form action="/api/auth/logout" method="post">
              <Button type="submit" variant="outline" size="sm">
                Logout
              </Button>
            </form>
          </div>
        </div>
      </nav>

      <main className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <h2 className="text-3xl font-bold text-gray-900 mb-2">Dashboard</h2>
          <p className="text-gray-600">Manage your resume, take quizzes, and discover opportunities</p>
        </div>

        <div className="grid lg:grid-cols-3 gap-6">
          {/* Left Column - Main Features */}
          <div className="lg:col-span-2 space-y-6">
            <ResumeUpload existingResume={latestResume} />

            {latestResume && (
              <>
                <ATSScoreDisplay
                  resumeId={latestResume.id}
                  existingScore={latestATSScore}
                />

                <QuizCard resumeId={latestResume.id} />

                <JobRecommendations resumeId={latestResume.id} />
              </>
            )}
          </div>

          {/* Right Column - Sidebar */}
          <div className="space-y-6">
            <LeaderboardPreview />

            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <Link href="/dashboard/resume">
                  <Button variant="outline" className="w-full justify-start">
                    View All Resumes
                  </Button>
                </Link>
                <Link href="/dashboard/quiz">
                  <Button variant="outline" className="w-full justify-start">
                    Take Quiz
                  </Button>
                </Link>
                <Link href="/dashboard/leaderboard">
                  <Button variant="outline" className="w-full justify-start">
                    View Leaderboard
                  </Button>
                </Link>
                <Link href="/dashboard/jobs">
                  <Button variant="outline" className="w-full justify-start">
                    Browse Jobs
                  </Button>
                </Link>
              </CardContent>
            </Card>
          </div>
        </div>
      </main>
    </div>
  );
}

