import OpenAI from 'openai';
import { ParsedResume } from './resume-parser';

const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

export interface QuizQuestion {
  id: string;
  question: string;
  options: string[];
  correctAnswer: number;
  difficulty: 'easy' | 'medium' | 'hard';
  explanation?: string;
}

export interface Quiz {
  role: string;
  questions: QuizQuestion[];
}

const ROLES = ['SDE', 'Frontend Developer', 'Backend Developer', 'Data Analyst', 'Full Stack Developer'];

export function identifyTopRoles(skills: string[]): string[] {
  const roleScores: Record<string, number> = {};

  ROLES.forEach(role => {
    roleScores[role] = 0;
  });

  const skillMap: Record<string, string[]> = {
    'SDE': ['java', 'python', 'c++', 'algorithms', 'data structures', 'system design'],
    'Frontend Developer': ['react', 'javascript', 'html', 'css', 'vue', 'angular', 'typescript'],
    'Backend Developer': ['node.js', 'python', 'java', 'api', 'database', 'server', 'rest'],
    'Data Analyst': ['python', 'sql', 'excel', 'statistics', 'data analysis', 'visualization'],
    'Full Stack Developer': ['react', 'node.js', 'javascript', 'typescript', 'database', 'api'],
  };

  skills.forEach(skill => {
    const lowerSkill = skill.toLowerCase();
    Object.entries(skillMap).forEach(([role, roleSkills]) => {
      if (roleSkills.some(rs => lowerSkill.includes(rs) || rs.includes(lowerSkill))) {
        roleScores[role]++;
      }
    });
  });

  // Get top 3 roles
  const sortedRoles = Object.entries(roleScores)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 3)
    .map(([role]) => role);

  return sortedRoles.length > 0 ? sortedRoles : ['SDE', 'Frontend Developer', 'Backend Developer'];
}

export async function generateQuizForRole(
  role: string,
  skills: string[],
  useAI: boolean = true
): Promise<QuizQuestion[]> {
  if (useAI) {
    try {
      return await generateQuizWithAI(role, skills);
    } catch (error) {
      console.error('AI quiz generation failed, using fallback:', error);
      return generateFallbackQuiz(role);
    }
  }

  return generateFallbackQuiz(role);
}

async function generateQuizWithAI(role: string, skills: string[]): Promise<QuizQuestion[]> {
  const prompt = `Generate 10 multiple-choice questions for a ${role} position.

The candidate has these skills: ${skills.join(', ')}

Create questions with:
- 3 easy questions (basic concepts)
- 4 medium questions (intermediate knowledge)
- 3 hard questions (advanced topics)

For each question, provide:
- A clear, concise question
- 4 answer options (only one correct)
- The correct answer index (0-3)
- A brief explanation

Return JSON format:
{
  "questions": [
    {
      "question": "string",
      "options": ["string", "string", "string", "string"],
      "correctAnswer": number (0-3),
      "difficulty": "easy" | "medium" | "hard",
      "explanation": "string"
    }
  ]
}`;

  const response = await openai.chat.completions.create({
    model: 'gpt-4-turbo-preview',
    messages: [
      {
        role: 'system',
        content: 'You are an expert technical interviewer. Generate high-quality technical questions.',
      },
      {
        role: 'user',
        content: prompt,
      },
    ],
    response_format: { type: 'json_object' },
    temperature: 0.7,
    max_tokens: 2000,
  });

  const content = response.choices[0]?.message?.content;
  if (content) {
    const data = JSON.parse(content);
    const questions = data.questions || [];

    return questions.map((q: any, index: number) => ({
      id: `q-${role}-${index}`,
      question: q.question,
      options: q.options,
      correctAnswer: q.correctAnswer,
      difficulty: q.difficulty || (index < 3 ? 'easy' : index < 7 ? 'medium' : 'hard'),
      explanation: q.explanation,
    }));
  }

  throw new Error('Failed to parse AI response');
}

function generateFallbackQuiz(role: string): QuizQuestion[] {
  const questions: QuizQuestion[] = [];

  // Easy questions
  questions.push(
    {
      id: `q-${role}-0`,
      question: `What is the primary purpose of version control systems like Git?`,
      options: [
        'To compile code',
        'To track changes in code and collaborate',
        'To deploy applications',
        'To write documentation',
      ],
      correctAnswer: 1,
      difficulty: 'easy',
      explanation: 'Version control systems track changes and enable collaboration.',
    },
    {
      id: `q-${role}-1`,
      question: `Which HTTP method is typically used for retrieving data?`,
      options: ['POST', 'GET', 'PUT', 'DELETE'],
      correctAnswer: 1,
      difficulty: 'easy',
      explanation: 'GET is used for retrieving data from a server.',
    },
    {
      id: `q-${role}-2`,
      question: `What does API stand for?`,
      options: [
        'Application Programming Interface',
        'Automated Program Integration',
        'Advanced Programming Interface',
        'Application Process Integration',
      ],
      correctAnswer: 0,
      difficulty: 'easy',
      explanation: 'API stands for Application Programming Interface.',
    }
  );

  // Medium questions
  questions.push(
    {
      id: `q-${role}-3`,
      question: `What is the time complexity of binary search?`,
      options: ['O(n)', 'O(log n)', 'O(n log n)', 'O(1)'],
      correctAnswer: 1,
      difficulty: 'medium',
      explanation: 'Binary search has O(log n) time complexity.',
    },
    {
      id: `q-${role}-4`,
      question: `Which of the following is NOT a NoSQL database?`,
      options: ['MongoDB', 'PostgreSQL', 'Cassandra', 'Redis'],
      correctAnswer: 1,
      difficulty: 'medium',
      explanation: 'PostgreSQL is a relational (SQL) database.',
    },
    {
      id: `q-${role}-5`,
      question: `What is the purpose of React hooks?`,
      options: [
        'To style components',
        'To manage state and side effects in functional components',
        'To create routes',
        'To optimize images',
      ],
      correctAnswer: 1,
      difficulty: 'medium',
      explanation: 'React hooks allow functional components to use state and lifecycle features.',
    },
    {
      id: `q-${role}-6`,
      question: `What is REST?`,
      options: [
        'A programming language',
        'A database system',
        'An architectural style for web services',
        'A testing framework',
      ],
      correctAnswer: 2,
      difficulty: 'medium',
      explanation: 'REST is an architectural style for designing web services.',
    }
  );

  // Hard questions
  questions.push(
    {
      id: `q-${role}-7`,
      question: `What is the difference between horizontal and vertical scaling?`,
      options: [
        'Horizontal scaling adds more servers, vertical scaling adds more power to existing servers',
        'They are the same thing',
        'Horizontal scaling is for databases only',
        'Vertical scaling is always better',
      ],
      correctAnswer: 0,
      difficulty: 'hard',
      explanation: 'Horizontal scaling adds more machines, vertical scaling upgrades existing machines.',
    },
    {
      id: `q-${role}-8`,
      question: `What is the CAP theorem?`,
      options: [
        'A theorem about code optimization',
        'A theorem stating you can only have 2 of: Consistency, Availability, Partition tolerance',
        'A theorem about API design',
        'A theorem about database normalization',
      ],
      correctAnswer: 1,
      difficulty: 'hard',
      explanation: 'CAP theorem states you can only guarantee 2 of 3: Consistency, Availability, Partition tolerance.',
    },
    {
      id: `q-${role}-9`,
      question: `What is the purpose of a reverse proxy?`,
      options: [
        'To hide server IP addresses and provide load balancing',
        'To encrypt data',
        'To store cache',
        'To compile code',
      ],
      correctAnswer: 0,
      difficulty: 'hard',
      explanation: 'Reverse proxies hide server details and can provide load balancing and SSL termination.',
    }
  );

  return questions;
}

