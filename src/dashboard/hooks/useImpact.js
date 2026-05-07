/**
 * Free Impact summary hook (Phase 6.2).
 */
import { useQuery } from '@tanstack/react-query';
import { impactApi } from '@DashboardApp/api/client';

export function useImpactSummary() {
	return useQuery( {
		queryKey: [ 'impact-summary' ],
		queryFn: () => impactApi.summary(),
		staleTime: 5 * 60 * 1000,
		refetchOnWindowFocus: false,
	} );
}
