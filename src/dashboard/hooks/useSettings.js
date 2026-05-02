/**
 * Settings data hooks.
 */
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { settingsApi, systemApi } from '@DashboardApp/api/client';

export function useSettings() {
	return useQuery( {
		queryKey: [ 'settings' ],
		queryFn: () => settingsApi.get(),
		staleTime: 10 * 60 * 1000,
		refetchOnWindowFocus: false,
	} );
}

export function useUpdateSettings() {
	const queryClient = useQueryClient();

	return useMutation( {
		mutationFn: ( data ) => settingsApi.update( data ),
		onSuccess: ( data ) => {
			queryClient.setQueryData( [ 'settings' ], data );
		},
	} );
}

export function useSendTestDigest() {
	const queryClient = useQueryClient();

	return useMutation( {
		mutationFn: ( recipient ) => systemApi.sendTestDigest( recipient ),
		onSuccess: () => {
			// Refetch settings so the "Last sent" line updates from SystemState.
			queryClient.invalidateQueries( { queryKey: [ 'settings' ] } );
		},
	} );
}
