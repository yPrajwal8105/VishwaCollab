import pdfParse from 'pdf-parse';
import mammoth from 'mammoth';

export interface ParsedResume {
  rawText: string;
  workExperience: Array<{
    company: string;
    position: string;
    duration: string;
    description: string;
  }>;
  skills: string[];
  education: Array<{
    institution: string;
    degree: string;
    field: string;
    year: string;
  }>;
  projects: Array<{
    name: string;
    description: string;
    technologies: string[];
  }>;
}

export async function parseResume(file: File): Promise<ParsedResume> {
  const fileType = file.type;
  let rawText = '';

  try {
    if (fileType === 'application/pdf') {
      const arrayBuffer = await file.arrayBuffer();
      const buffer = Buffer.from(arrayBuffer);
      const pdfData = await pdfParse(buffer);
      rawText = pdfData.text;
    } else if (
      fileType === 'application/msword' ||
      fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ) {
      const arrayBuffer = await file.arrayBuffer();
      const result = await mammoth.extractRawText({ arrayBuffer });
      rawText = result.value;
    } else {
      throw new Error('Unsupported file type');
    }

    // Parse the extracted text
    return parseResumeText(rawText);
  } catch (error) {
    console.error('Error parsing resume:', error);
    throw new Error('Failed to parse resume. Please ensure the file is valid.');
  }
}

function parseResumeText(text: string): ParsedResume {
  const lines = text.split('\n').map(line => line.trim()).filter(line => line.length > 0);
  
  // Extract skills (common patterns)
  const skills = extractSkills(text);
  
  // Extract work experience
  const workExperience = extractWorkExperience(text);
  
  // Extract education
  const education = extractEducation(text);
  
  // Extract projects
  const projects = extractProjects(text);

  return {
    rawText: text,
    workExperience,
    skills,
    education,
    projects,
  };
}

function extractSkills(text: string): string[] {
  const skillKeywords = [
    'JavaScript', 'TypeScript', 'Python', 'Java', 'C++', 'C#', 'Go', 'Rust',
    'React', 'Vue', 'Angular', 'Node.js', 'Express', 'Next.js', 'Django', 'Flask',
    'MongoDB', 'PostgreSQL', 'MySQL', 'Redis', 'AWS', 'Docker', 'Kubernetes',
    'Git', 'CI/CD', 'Machine Learning', 'Data Science', 'TensorFlow', 'PyTorch',
    'HTML', 'CSS', 'SASS', 'TailwindCSS', 'Bootstrap', 'GraphQL', 'REST API',
    'Agile', 'Scrum', 'DevOps', 'Linux', 'System Design', 'Microservices'
  ];

  const foundSkills: string[] = [];
  const lowerText = text.toLowerCase();

  skillKeywords.forEach(skill => {
    if (lowerText.includes(skill.toLowerCase())) {
      foundSkills.push(skill);
    }
  });

  // Also look for skills section
  const skillsSectionRegex = /(?:skills?|technical skills?|technologies?)[:]\s*([^\n]+(?:\n[^\n]+)*?)(?=\n\n|\n[A-Z]|$)/i;
  const skillsMatch = text.match(skillsSectionRegex);
  if (skillsMatch) {
    const skillsText = skillsMatch[1];
    const skillsList = skillsText.split(/[,;|•\-\n]/).map(s => s.trim()).filter(s => s.length > 0);
    foundSkills.push(...skillsList);
  }

  return [...new Set(foundSkills)];
}

function extractWorkExperience(text: string): Array<{
  company: string;
  position: string;
  duration: string;
  description: string;
}> {
  const experience: Array<{
    company: string;
    position: string;
    duration: string;
    description: string;
  }> = [];

  // Look for experience section
  const experienceRegex = /(?:experience|work experience|employment|professional experience)[:]\s*(.*?)(?=\n\n(?:education|projects|skills)|$)/is;
  const experienceMatch = text.match(experienceRegex);

  if (experienceMatch) {
    const experienceText = experienceMatch[1];
    const entries = experienceText.split(/\n(?=[A-Z])/);

    entries.forEach(entry => {
      const lines = entry.split('\n').filter(l => l.trim().length > 0);
      if (lines.length >= 2) {
        const firstLine = lines[0];
        const positionMatch = firstLine.match(/^(.+?)(?:\s+at\s+|\s+@\s+|\s+-\s+)(.+)$/i);
        
        if (positionMatch) {
          experience.push({
            position: positionMatch[1].trim(),
            company: positionMatch[2].trim(),
            duration: lines[1] || '',
            description: lines.slice(2).join(' '),
          });
        }
      }
    });
  }

  return experience;
}

function extractEducation(text: string): Array<{
  institution: string;
  degree: string;
  field: string;
  year: string;
}> {
  const education: Array<{
    institution: string;
    degree: string;
    field: string;
    year: string;
  }> = [];

  const educationRegex = /(?:education|academic|qualifications?)[:]\s*(.*?)(?=\n\n(?:experience|projects|skills)|$)/is;
  const educationMatch = text.match(educationRegex);

  if (educationMatch) {
    const educationText = educationMatch[1];
    const entries = educationText.split(/\n(?=[A-Z])/);

    entries.forEach(entry => {
      const lines = entry.split('\n').filter(l => l.trim().length > 0);
      if (lines.length >= 1) {
        const firstLine = lines[0];
        const degreeMatch = firstLine.match(/(.+?)(?:\s+from\s+|\s+@\s+|\s+-\s+)(.+)$/i);
        
        if (degreeMatch) {
          education.push({
            degree: degreeMatch[1].trim(),
            institution: degreeMatch[2].trim(),
            field: lines[1] || '',
            year: lines[2] || '',
          });
        }
      }
    });
  }

  return education;
}

function extractProjects(text: string): Array<{
  name: string;
  description: string;
  technologies: string[];
}> {
  const projects: Array<{
    name: string;
    description: string;
    technologies: string[];
  }> = [];

  const projectsRegex = /(?:projects?|personal projects?)[:]\s*(.*?)(?=\n\n(?:experience|education|skills)|$)/is;
  const projectsMatch = text.match(projectsRegex);

  if (projectsMatch) {
    const projectsText = projectsMatch[1];
    const entries = projectsText.split(/\n(?=[A-Z])/);

    entries.forEach(entry => {
      const lines = entry.split('\n').filter(l => l.trim().length > 0);
      if (lines.length >= 1) {
        projects.push({
          name: lines[0],
          description: lines.slice(1).join(' '),
          technologies: extractSkills(entry),
        });
      }
    });
  }

  return projects;
}

