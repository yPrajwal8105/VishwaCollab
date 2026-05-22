import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

export default function HomePage() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <nav className="border-b bg-white/80 backdrop-blur-sm">
        <div className="container mx-auto px-4 py-4 flex justify-between items-center">
          <h1 className="text-2xl font-bold text-indigo-600">VishwaCollab</h1>
          <div className="flex gap-4">
            <Link href="/login">
              <Button variant="ghost">Login</Button>
            </Link>
            <Link href="/signup">
              <Button>Sign Up</Button>
            </Link>
          </div>
        </div>
      </nav>

      <main className="container mx-auto px-4 py-16">
        <div className="text-center mb-16">
          <h2 className="text-5xl font-bold text-gray-900 mb-4">
            Professional Career Platform
          </h2>
          <p className="text-xl text-gray-600 mb-8">
            Upload your resume, get ATS scores, take skill quizzes, and discover job opportunities
          </p>
          <Link href="/signup">
            <Button size="lg" className="text-lg px-8 py-6">
              Get Started
            </Button>
          </Link>
        </div>

        <div className="grid md:grid-cols-3 gap-8 mt-16">
          <Card>
            <CardHeader>
              <CardTitle>Resume Analysis</CardTitle>
              <CardDescription>
                Upload your resume and get instant ATS scoring with detailed feedback
              </CardDescription>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 text-sm text-gray-600">
                <li>✓ PDF & DOCX support</li>
                <li>✓ Automatic parsing</li>
                <li>✓ Skill extraction</li>
                <li>✓ ATS compatibility score</li>
              </ul>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Skill Quizzes</CardTitle>
              <CardDescription>
                Take AI-generated quizzes based on your resume skills
              </CardDescription>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 text-sm text-gray-600">
                <li>✓ Role-based questions</li>
                <li>✓ Multiple difficulty levels</li>
                <li>✓ Instant feedback</li>
                <li>✓ Leaderboard rankings</li>
              </ul>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Job Recommendations</CardTitle>
              <CardDescription>
                Get personalized job recommendations based on your skills
              </CardDescription>
            </CardHeader>
            <CardContent>
              <ul className="space-y-2 text-sm text-gray-600">
                <li>✓ Skill-based matching</li>
                <li>✓ Location preferences</li>
                <li>✓ Real-time job data</li>
                <li>✓ Smart caching</li>
              </ul>
            </CardContent>
          </Card>
        </div>
      </main>
    </div>
  );
}

