'use client';

import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

export default function LeaderboardPage() {
  const [leaderboard, setLeaderboard] = useState<any[]>([]);
  const [selectedRole, setSelectedRole] = useState<string>('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchLeaderboard();
  }, [selectedRole]);

  const fetchLeaderboard = async () => {
    setLoading(true);
    try {
      const url = selectedRole
        ? `/api/leaderboard?role=${encodeURIComponent(selectedRole)}&limit=50`
        : '/api/leaderboard?limit=50';
      const response = await fetch(url);
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
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="container mx-auto px-4">
        <Card>
          <CardHeader>
            <CardTitle>Leaderboard</CardTitle>
            <CardDescription>Top performers across all roles</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="mb-4">
              <label className="block text-sm font-medium mb-2">Filter by Role</label>
              <select
                value={selectedRole}
                onChange={(e) => setSelectedRole(e.target.value)}
                className="w-full p-2 border rounded-md"
              >
                <option value="">All Roles</option>
                <option value="SDE">SDE</option>
                <option value="Frontend Developer">Frontend Developer</option>
                <option value="Backend Developer">Backend Developer</option>
                <option value="Data Analyst">Data Analyst</option>
                <option value="Full Stack Developer">Full Stack Developer</option>
              </select>
            </div>

            {loading ? (
              <p className="text-center py-8">Loading...</p>
            ) : leaderboard.length === 0 ? (
              <p className="text-center py-8 text-gray-500">No scores yet. Be the first!</p>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b">
                      <th className="text-left p-2">Rank</th>
                      <th className="text-left p-2">Name</th>
                      <th className="text-left p-2">Role</th>
                      <th className="text-right p-2">Score</th>
                    </tr>
                  </thead>
                  <tbody>
                    {leaderboard.map((entry, index) => (
                      <tr key={entry.id} className="border-b hover:bg-gray-50">
                        <td className="p-2 font-medium">#{index + 1}</td>
                        <td className="p-2">{entry.users?.name || 'Anonymous'}</td>
                        <td className="p-2">{entry.role}</td>
                        <td className="p-2 text-right font-bold text-indigo-600">
                          {entry.score.toFixed(1)}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

