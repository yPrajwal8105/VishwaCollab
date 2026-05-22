'use client';

import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface ATSScoreDisplayProps {
  resumeId: string;
  existingScore?: any;
}

export default function ATSScoreDisplay({ resumeId, existingScore }: ATSScoreDisplayProps) {
  const [loading, setLoading] = useState(false);
  const [score, setScore] = useState(existingScore);
  const [jobDescription, setJobDescription] = useState('');
  const [role, setRole] = useState('');

  const calculateScore = async () => {
    setLoading(true);
    try {
      const response = await fetch('/api/ats/score', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          resumeId,
          jobDescription: jobDescription || undefined,
          role: role || undefined,
        }),
      });

      const data = await response.json();
      if (data.success) {
        setScore(data.score);
      }
    } catch (error) {
      console.error('Error calculating ATS score:', error);
    } finally {
      setLoading(false);
    }
  };

  const scoreValue = score?.overall_score || 0;
  const scoreColor =
    scoreValue >= 80 ? 'text-green-600' : scoreValue >= 60 ? 'text-yellow-600' : 'text-red-600';

  return (
    <Card>
      <CardHeader>
        <CardTitle>ATS Score</CardTitle>
        <CardDescription>
          Get your resume's ATS compatibility score
        </CardDescription>
      </CardHeader>
      <CardContent>
        {score && (
          <div className="mb-6">
            <div className="text-center mb-4">
              <div className={`text-6xl font-bold ${scoreColor} mb-2`}>
                {scoreValue}
              </div>
              <p className="text-sm text-gray-600">Overall ATS Score</p>
              <p className="text-xs text-gray-500 mt-1">
                Keyword Match: {score.keyword_match_percentage.toFixed(1)}%
              </p>
            </div>

            {score.missing_skills && score.missing_skills.length > 0 && (
              <div className="mb-4">
                <p className="text-sm font-medium mb-2">Missing Skills:</p>
                <div className="flex flex-wrap gap-2">
                  {score.missing_skills.map((skill: string, idx: number) => (
                    <span
                      key={idx}
                      className="px-2 py-1 bg-red-100 text-red-800 text-xs rounded"
                    >
                      {skill}
                    </span>
                  ))}
                </div>
              </div>
            )}

            {score.suggestions && score.suggestions.length > 0 && (
              <div>
                <p className="text-sm font-medium mb-2">Suggestions:</p>
                <ul className="space-y-2">
                  {score.suggestions.map((suggestion: any, idx: number) => (
                    <li key={idx} className="text-sm text-gray-700">
                      <span className="font-medium">{suggestion.category}:</span>{' '}
                      {suggestion.suggestion}
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </div>
        )}

        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-2">
              Job Description (Optional)
            </label>
            <textarea
              value={jobDescription}
              onChange={(e) => setJobDescription(e.target.value)}
              className="w-full p-2 border rounded-md text-sm"
              rows={3}
              placeholder="Paste job description here..."
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-2">Role (Optional)</label>
            <input
              type="text"
              value={role}
              onChange={(e) => setRole(e.target.value)}
              className="w-full p-2 border rounded-md text-sm"
              placeholder="e.g., Frontend Developer, SDE"
            />
          </div>

          <Button onClick={calculateScore} disabled={loading} className="w-full">
            {loading ? 'Calculating...' : 'Calculate ATS Score'}
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}

