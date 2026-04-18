/**
 * History data hook.
 *
 * Fetches paginated daily diagnosis history.
 */
import { useQuery } from '@tanstack/react-query';
import { historyApi } from '@DashboardApp/api/client';

export function useHistory( page = 1, perPage = 14 ) {
	return useQuery( {
		queryKey: [ 'history', page, perPage ],
		queryFn: () => historyApi.list( page, perPage ),
		staleTime: 5 * 60 * 1000,
		keepPreviousData: true,
		refetchOnWindowFocus: false,
	} );
}
