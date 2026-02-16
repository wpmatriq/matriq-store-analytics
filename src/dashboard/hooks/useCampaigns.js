/**
 * Campaigns data hooks.
 *
 * CRUD operations for campaign context layer.
 */
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { campaignsApi } from '@DashboardApp/api/client';

export function useCampaigns() {
	return useQuery( {
		queryKey: [ 'campaigns' ],
		queryFn: () => campaignsApi.list(),
		staleTime: 2 * 60 * 1000,
		refetchOnWindowFocus: false,
	} );
}

export function useCreateCampaign() {
	const queryClient = useQueryClient();

	return useMutation( {
		mutationFn: ( data ) => campaignsApi.create( data ),
		onSuccess: () => {
			queryClient.invalidateQueries( { queryKey: [ 'campaigns' ] } );
			queryClient.invalidateQueries( { queryKey: [ 'overview' ] } );
		},
	} );
}

export function useEndCampaign() {
	const queryClient = useQueryClient();

	return useMutation( {
		mutationFn: ( id ) => campaignsApi.end( id ),
		onSuccess: () => {
			queryClient.invalidateQueries( { queryKey: [ 'campaigns' ] } );
			queryClient.invalidateQueries( { queryKey: [ 'overview' ] } );
		},
	} );
}

export function useDeleteCampaign() {
	const queryClient = useQueryClient();

	return useMutation( {
		mutationFn: ( id ) => campaignsApi.remove( id ),
		onSuccess: () => {
			queryClient.invalidateQueries( { queryKey: [ 'campaigns' ] } );
		},
	} );
}
