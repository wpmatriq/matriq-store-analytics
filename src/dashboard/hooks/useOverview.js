/**
 * Overview data hook.
 *
 * Fetches morning briefing data (diagnosis, metrics, recommendation).
 */
import { useQuery } from '@tanstack/react-query';
import { overviewApi } from '@DashboardApp/api/client';

export function useOverview( period = 'daily' ) {
	return useQuery( {
		queryKey: [ 'overview', period ],
		queryFn: () => overviewApi.get( period ),
		staleTime: 5 * 60 * 1000, // 5 minutes.
		refetchOnWindowFocus: false,
	} );
}

export function useTrend( days = 7 ) {
	return useQuery( {
		queryKey: [ 'trend', days ],
		queryFn: () => overviewApi.trend( days ),
		staleTime: 10 * 60 * 1000, // 10 minutes.
		refetchOnWindowFocus: false,
	} );
}
