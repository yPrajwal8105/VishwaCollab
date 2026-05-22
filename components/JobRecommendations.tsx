'use client';

import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface JobRecommendationsProps {
  resumeId: string;
}

interface Job {
  id: string;
  title: string;
  company: string;
  location: string;
  description: string;
  url: string;
}

export default function JobRecommendations({ resumeId }: JobRecommendationsProps) {
  const [loading, setLoading] = useState(false);
  const [jobs, setJobs] = useState<Job[]>([]);
  const [location, setLocation] = useState('us');

  const fetchJobs = async () => {
    setLoading(true);
    try {
      const response = await fetch('/api/jobs/recommend', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ resumeId, location }),
      });

      const data = await response.json();
      if (data.success) {
        setJobs(data.jobs || []);
      }
    } catch (error) {
      console.error('Error fetching jobs:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (resumeId) {
      fetchJobs();
    }
  }, [resumeId, location]);

  return (
    <Card>
      <CardHeader>
        <CardTitle>Job Recommendations</CardTitle>
        <CardDescription>
          Discover job opportunities based on your skills
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div className="mb-4">
          <label className="block text-sm font-medium mb-2">Location</label>
          <select
            value={location}
            onChange={(e) => setLocation(e.target.value)}
            className="w-full p-2 border rounded-md text-sm"
          >
            <option value="us">United States</option>
            <option value="gb">United Kingdom</option>
            <option value="ca">Canada</option>
            <option value="au">Australia</option>
            <option value="in">India</option>
          </select>
        </div>

        <Button onClick={fetchJobs} disabled={loading} className="w-full mb-4">
          {loading ? 'Loading...' : 'Refresh Jobs'}
        </Button>

        <div className="space-y-4 max-h-96 overflow-y-auto">
          {jobs.length === 0 && !loading && (
            <p className="text-sm text-gray-500 text-center py-4">
              No jobs found. Try adjusting your location or skills.
            </p>
          )}

          {jobs.map((job) => (
            <div key={job.id} className="p-4 border rounded-md hover:bg-gray-50">
              <h4 className="font-medium text-sm mb-1">{job.title}</h4>
              <p className="text-xs text-gray-600 mb-2">{job.company} • {job.location}</p>
              <p className="text-xs text-gray-500 mb-3 line-clamp-2">
                {job.description.substring(0, 150)}...
              </p>
              <a
                href={job.url}
                target="_blank"
                rel="noopener noreferrer"
                className="text-xs text-indigo-600 hover:underline"
              >
                View Job →
              </a>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}

