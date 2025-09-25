import React from 'react';
import SettingsRoute from '@DashboardApp/Pages/SettingsRoute';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

const Wrapper = () => {
	const queryClient = new QueryClient();
	return (
		<div className="wc-sma-application-main-container">
			<div className="portals-setting-content flex flex-col">
				<div className="md:w-full lg:w-full portals-setting-content-inner">
					<QueryClientProvider client={ queryClient }>
						<SettingsRoute />
					</QueryClientProvider>
				</div>
			</div>
		</div>
	);
};

export default Wrapper;
