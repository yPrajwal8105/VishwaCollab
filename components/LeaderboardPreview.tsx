'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import Link from 'next/link';
import { Button } from '@/components/ui/button';

export default function LeaderboardPreview() {
  const [leaderboard, setLeaderboard] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchLeaderboard();
  }, []);

  const fetchLeaderboard = async () => {
    try {
      const response = await fetch('/api/leaderboard?limit=5');
      const data = await response.json();
      if (data.success) {
        setLeaderboard(data.leaderboard || []);
      }
    } catch (error) {
      console.error('Error fetching leaderboard:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>Leaderboard</CardTitle>
        <CardDescription>Top performers</CardDescription>
      </CardHeader>
      <CardContent>
        {loading ? (
          <p className="text-sm text-gray-500">Loading...</p>
        ) : leaderboard.length === 0 ? (
          <p className="text-sm text-gray-500">No scores yet. Be the first!</p>
        ) : (
          <div className="space-y-2">
            {leaderboard.map((entry, index) => (
              <div
                key={entry.id}
                className="flex items-center justify-between p-2 bg-gray-50 rounded"
              >
                <div className="flex items-center gap-2">
                  <span className="text-sm font-medium text-gray-400 w-6">
                    #{index + 1}
                  </span>
                  <span className="text-sm font-medium">
                    {entry.users?.name || 'Anonymous'}
                  </span>
                </div>
                <span className="text-sm font-bold text-indigo-600">
                  {entry.score.toFixed(1)}
                </span>
              </div>
            ))}
          </div>
        )}

        <Link href="/dashboard/leaderboard" className="block mt-4">
          <Button variant="outline" className="w-full">
            View Full Leaderboard
          </Button>
        </Link>
      </CardContent>
    </Card>
  );
}

