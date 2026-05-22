'use client';

import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import Link from 'next/link';

interface QuizCardProps {
  resumeId: string;
}

export default function QuizCard({ resumeId }: QuizCardProps) {
  const [generating, setGenerating] = useState(false);
  const [quizGenerated, setQuizGenerated] = useState(false);

  const generateQuiz = async () => {
    setGenerating(true);
    try {
      const response = await fetch('/api/quiz/generate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ resumeId }),
      });

      const data = await response.json();
      if (data.success) {
        setQuizGenerated(true);
        // Redirect to quiz page
        window.location.href = `/dashboard/quiz?role=${encodeURIComponent(data.role)}`;
      }
    } catch (error) {
      console.error('Error generating quiz:', error);
    } finally {
      setGenerating(false);
    }
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>Skill Quiz</CardTitle>
        <CardDescription>
          Take an AI-generated quiz based on your resume skills
        </CardDescription>
      </CardHeader>
      <CardContent>
        <p className="text-sm text-gray-600 mb-4">
          Get personalized quiz questions based on your skills and preferred role.
          Test your knowledge and compete on the leaderboard!
        </p>

        <Button
          onClick={generateQuiz}
          disabled={generating}
          className="w-full"
        >
          {generating ? 'Generating Quiz...' : 'Generate & Start Quiz'}
        </Button>

        <Link href="/dashboard/quiz">
          <Button variant="outline" className="w-full mt-2">
            View Previous Quizzes
          </Button>
        </Link>
      </CardContent>
    </Card>
  );
}

