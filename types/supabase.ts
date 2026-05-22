export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[];

export interface Database {
  public: {
    Tables: {
      users: {
        Row: {
          id: string;
          email: string;
          name: string;
          role: 'student' | 'company' | 'tpo';
          created_at: string;
          updated_at: string;
        };
        Insert: {
          id?: string;
          email: string;
          name: string;
          role: 'student' | 'company' | 'tpo';
          created_at?: string;
          updated_at?: string;
        };
        Update: {
          id?: string;
          email?: string;
          name?: string;
          role?: 'student' | 'company' | 'tpo';
          created_at?: string;
          updated_at?: string;
        };
      };
      resumes: {
        Row: {
          id: string;
          user_id: string;
          file_name: string;
          file_path: string;
          file_size: number;
          file_type: string;
          uploaded_at: string;
          created_at: string;
        };
        Insert: {
          id?: string;
          user_id: string;
          file_name: string;
          file_path: string;
          file_size: number;
          file_type: string;
          uploaded_at?: string;
          created_at?: string;
        };
        Update: {
          id?: string;
          user_id?: string;
          file_name?: string;
          file_path?: string;
          file_size?: number;
          file_type?: string;
          uploaded_at?: string;
          created_at?: string;
        };
      };
      parsed_resume_data: {
        Row: {
          id: string;
          resume_id: string;
          user_id: string;
          raw_text: string;
          work_experience: Json;
          skills: string[];
          education: Json;
          projects: Json;
          embeddings: number[] | null;
          parsed_at: string;
          created_at: string;
        };
        Insert: {
          id?: string;
          resume_id: string;
          user_id: string;
          raw_text: string;
          work_experience: Json;
          skills: string[];
          education: Json;
          projects: Json;
          embeddings?: number[] | null;
          parsed_at?: string;
          created_at?: string;
        };
        Update: {
          id?: string;
          resume_id?: string;
          user_id?: string;
          raw_text?: string;
          work_experience?: Json;
          skills?: string[];
          education?: Json;
          projects?: Json;
          embeddings?: number[] | null;
          parsed_at?: string;
          created_at?: string;
        };
      };
      quiz_questions: {
        Row: {
          id: string;
          role: string;
          question: string;
          options: Json;
          correct_answer: number;
          difficulty: 'easy' | 'medium' | 'hard';
          created_at: string;
        };
        Insert: {
          id?: string;
          role: string;
          question: string;
          options: Json;
          correct_answer: number;
          difficulty: 'easy' | 'medium' | 'hard';
          created_at?: string;
        };
        Update: {
          id?: string;
          role?: string;
          question?: string;
          options?: Json;
          correct_answer?: number;
          difficulty?: 'easy' | 'medium' | 'hard';
          created_at?: string;
        };
      };
      quiz_results: {
        Row: {
          id: string;
          user_id: string;
          role: string;
          score: number;
          total_questions: number;
          correct_answers: number;
          time_taken: number;
          answers: Json;
          completed_at: string;
          created_at: string;
        };
        Insert: {
          id?: string;
          user_id: string;
          role: string;
          score: number;
          total_questions: number;
          correct_answers: number;
          time_taken: number;
          answers: Json;
          completed_at?: string;
          created_at?: string;
        };
        Update: {
          id?: string;
          user_id?: string;
          role?: string;
          score?: number;
          total_questions?: number;
          correct_answers?: number;
          time_taken?: number;
          answers?: Json;
          completed_at?: string;
          created_at?: string;
        };
      };
      leaderboard: {
        Row: {
          id: string;
          user_id: string;
          role: string;
          score: number;
          quiz_result_id: string;
          created_at: string;
        };
        Insert: {
          id?: string;
          user_id: string;
          role: string;
          score: number;
          quiz_result_id: string;
          created_at?: string;
        };
        Update: {
          id?: string;
          user_id?: string;
          role?: string;
          score?: number;
          quiz_result_id?: string;
          created_at?: string;
        };
      };
      job_cache: {
        Row: {
          id: string;
          user_id: string;
          location: string;
          skills: string[];
          jobs: Json;
          cached_at: string;
          expires_at: string;
          created_at: string;
        };
        Insert: {
          id?: string;
          user_id: string;
          location: string;
          skills: string[];
          jobs: Json;
          cached_at?: string;
          expires_at: string;
          created_at?: string;
        };
        Update: {
          id?: string;
          user_id?: string;
          location?: string;
          skills?: string[];
          jobs?: Json;
          cached_at?: string;
          expires_at?: string;
          created_at?: string;
        };
      };
      ats_scores: {
        Row: {
          id: string;
          user_id: string;
          resume_id: string;
          job_description: string | null;
          role: string | null;
          overall_score: number;
          keyword_match_percentage: number;
          missing_skills: string[];
          suggestions: Json;
          created_at: string;
        };
        Insert: {
          id?: string;
          user_id: string;
          resume_id: string;
          job_description?: string | null;
          role?: string | null;
          overall_score: number;
          keyword_match_percentage: number;
          missing_skills: string[];
          suggestions: Json;
          created_at?: string;
        };
        Update: {
          id?: string;
          user_id?: string;
          resume_id?: string;
          job_description?: string | null;
          role?: string | null;
          overall_score?: number;
          keyword_match_percentage?: number;
          missing_skills?: string[];
          suggestions?: Json;
          created_at?: string;
        };
      };
    };
    Views: {
      [_ in never]: never;
    };
    Functions: {
      [_ in never]: never;
    };
    Enums: {
      user_role: 'student' | 'company' | 'tpo';
      difficulty_level: 'easy' | 'medium' | 'hard';
    };
  };
}

