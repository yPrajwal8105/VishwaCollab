import { NextRequest, NextResponse } from 'next/server';
import { createClient } from '@/lib/supabase/server';
import { parseResume } from '@/lib/resume-parser';
import { withRetry, withRateLimit } from '@/lib/api-utils';

export async function POST(request: NextRequest) {
  try {
    const supabase = await createClient();
    const {
      data: { user },
    } = await supabase.auth.getUser();

    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const formData = await request.formData();
    const file = formData.get('file') as File;

    if (!file) {
      return NextResponse.json({ error: 'No file provided' }, { status: 400 });
    }

    // Validate file type
    const allowedTypes = [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    if (!allowedTypes.includes(file.type)) {
      return NextResponse.json(
        { error: 'Invalid file type. Please upload PDF or DOCX.' },
        { status: 400 }
      );
    }

    // Validate file size (10MB max)
    const maxSize = 10 * 1024 * 1024;
    if (file.size > maxSize) {
      return NextResponse.json(
        { error: 'File size exceeds 10MB limit' },
        { status: 400 }
      );
    }

    // Upload file to Supabase Storage
    const fileExt = file.name.split('.').pop();
    const fileName = `${user.id}/${Date.now()}.${fileExt}`;

    const arrayBuffer = await file.arrayBuffer();
    const buffer = Buffer.from(arrayBuffer);

    const { data: uploadData, error: uploadError } = await supabase.storage
      .from('resumes')
      .upload(fileName, buffer, {
        contentType: file.type,
        upsert: false,
      });

    if (uploadError) {
      console.error('Upload error:', uploadError);
      return NextResponse.json(
        { error: 'Failed to upload file' },
        { status: 500 }
      );
    }

    // Get public URL
    const {
      data: { publicUrl },
    } = supabase.storage.from('resumes').getPublicUrl(fileName);

    // Save resume record
    const { data: resumeData, error: resumeError } = await supabase
      .from('resumes')
      .insert({
        user_id: user.id,
        file_name: file.name,
        file_path: fileName,
        file_size: file.size,
        file_type: file.type,
      })
      .select()
      .single();

    if (resumeError) {
      console.error('Resume save error:', resumeError);
      return NextResponse.json(
        { error: 'Failed to save resume record' },
        { status: 500 }
      );
    }

    // Parse resume with retry logic
    const parsedResume = await withRetry(
      () => parseResume(file),
      { maxRetries: 2 }
    );

    // Save parsed data
    const { data: parsedData, error: parsedError } = await supabase
      .from('parsed_resume_data')
      .insert({
        resume_id: resumeData.id,
        user_id: user.id,
        raw_text: parsedResume.rawText,
        work_experience: parsedResume.workExperience,
        skills: parsedResume.skills,
        education: parsedResume.education,
        projects: parsedResume.projects,
      })
      .select()
      .single();

    if (parsedError) {
      console.error('Parsed data save error:', parsedError);
      return NextResponse.json(
        { error: 'Failed to save parsed data' },
        { status: 500 }
      );
    }

    return NextResponse.json({
      success: true,
      resume: resumeData,
      parsedData,
      publicUrl,
    });
  } catch (error) {
    console.error('Resume upload error:', error);
    return NextResponse.json(
      { error: 'Internal server error' },
      { status: 500 }
    );
  }
}

