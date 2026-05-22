import { NextRequest, NextResponse } from 'next/server';
import { createClient } from '@/lib/supabase/server';

export async function GET(request: NextRequest) {
  try {
    const supabase = await createClient();
    const {
      data: { user },
    } = await supabase.auth.getUser();

    const searchParams = request.nextUrl.searchParams;
    const role = searchParams.get('role');
    const limit = parseInt(searchParams.get('limit') || '10');

    let query = supabase
      .from('leaderboard')
      .select(`
        *,
        users:user_id (
          id,
          name,
          email
        )
      `)
      .order('score', { ascending: false })
      .limit(limit);

    if (role) {
      query = query.eq('role', role);
    }

    const { data: leaderboard, error } = await query;

    if (error) {
      console.error('Leaderboard fetch error:', error);
      return NextResponse.json(
        { error: 'Failed to fetch leaderboard' },
        { status: 500 }
      );
    }

    // Get user's rank if authenticated
    let userRank = null;
    if (user) {
      let rankQuery = supabase
        .from('leaderboard')
        .select('score', { count: 'exact', head: false })
        .order('score', { ascending: false });

      if (role) {
        rankQuery = rankQuery.eq('role', role);
      }

      const { data: userScores, error: rankError } = await supabase
        .from('leaderboard')
        .select('score')
        .eq('user_id', user.id)
        .order('score', { ascending: false })
        .limit(1)
        .single();

      if (!rankError && userScores) {
        const { count } = await supabase
          .from('leaderboard')
          .select('*', { count: 'exact', head: true })
          .gt('score', userScores.score);

        if (role) {
          const { count: roleCount } = await supabase
            .from('leaderboard')
            .select('*', { count: 'exact', head: true })
            .eq('role', role)
            .gt('score', userScores.score);

          userRank = (roleCount || 0) + 1;
        } else {
          userRank = (count || 0) + 1;
        }
      }
    }

    return NextResponse.json({
      success: true,
      leaderboard: leaderboard || [],
      userRank,
    });
  } catch (error) {
    console.error('Leaderboard error:', error);
    return NextResponse.json(
      { error: 'Internal server error' },
      { status: 500 }
    );
  }
}

