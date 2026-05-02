/**
 * Data readiness hook.
 *
 * Checks if all prerequisites are met before showing the dashboard.
 */
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { systemApi } from '@DashboardApp/api/client';

export function useReadiness() {
	return useQuery( {
		queryKey: [ 'readiness' ],
		queryFn: () => systemApi.readiness(),
		staleTime: 30 * 1000, // 30 seconds - check often during setup.
		refetchOnWindowFocus: true,
	} );
}

export function useTriggerSnapshot() {
	const queryClient = useQueryClient();

	return useMutation( {
		mutationFn: ( date ) => systemApi.triggerSnapshot( date ),
		onSuccess: () => {
			queryClient.invalidateQueries( { queryKey: [ 'readiness' ] } );
			queryClient.invalidateQueries( { queryKey: [ 'overview' ] } );
			queryClient.invalidateQueries( { queryKey: [ 'history' ] } );
		},
	} );
}

export function useTriggerBackfill() {
	const queryClient = useQueryClient();

	return useMutation( {
		mutationFn: () => systemApi.triggerBackfill(),
		onSuccess: () => {
			queryClient.invalidateQueries( { queryKey: [ 'readiness' ] } );
		},
	} );
}
