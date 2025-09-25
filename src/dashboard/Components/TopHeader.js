import { __ } from '@wordpress/i18n';
import { Fragment } from 'react';
import BrandIcon from '@AppImages/crown.svg';
import { useDispatch, useSelect } from '@wordpress/data';
import { Tooltip } from '@wordpress/components';
import { STORE_NAME } from '@Store/constants';

const CoreVersion = () => (
	<>
		<div className="flex items-center">
			<Tooltip text={ __( 'CORE', 'suredash' ) }
				delay={ 100 }
				className="z-999999 bg-black text-white shadow-md p-2 rounded-md"
			>
				<span>V-{ wc_sma_admin_data.version }</span>
			</Tooltip>
		</div>

		{ wc_sma_admin_data.pro_available && (
			<div className="flex items-center">
				<span>{ wc_sma_admin_data.pro_version }</span>
				<span className="ml-1 sm:ml-2 text-[0.625rem] leading-[1rem] font-medium text-white border border-slate-800 bg-slate-800 rounded-[0.1875rem] relative inline-flex flex-shrink-0 py-[0rem] px-1.5">
					{ ' ' }
					{ __( 'PRO', 'suredash' ) }{ ' ' }
				</span>
			</div>
		) }

		{ wp.hooks.applyFilters(
			'wc_smart_analytics_dashboard.after_navigation_version',
			<span />
		) }
	</>
);

export default function TopHeader() {
	const navMenus = [
		{
			name: __( 'Welcome', 'suredash' ),
			slug: wc_sma_admin_data.home_slug,
			path: 'home',
		},
		{
			name: __( 'Settings', 'suredash' ),
			slug: wc_sma_admin_data.home_slug,
			path: 'settings',
		},
		{
			name: __( 'Free vs Pro', 'suredash' ),
			slug: wc_sma_admin_data.home_slug,
			path: 'free-vs-pro',
		},
	];

	const redirectToProPurchase = () => {
		window.open(
			wc_sma_admin_data.upgrade_link,
			'_blank'
		);
	};

	const menus = wp.hooks.applyFilters( 'wc_smart_analytics_dashboard.main_navigation', navMenus );

	const { navigateTo } = useDispatch( STORE_NAME );
	const { activeTab } = useSelect( ( select ) => {
		const { getActiveTab } = select( STORE_NAME );
		return { activeTab: getActiveTab() };
	} );

	return (
		<section className="bg-white header-nav">
			<div className="max-w-3xl mx-auto px-3 sm:px-6 lg:max-w-full">
				<div className="relative flex flex-col lg:flex-row justify-between h-28 lg:h-16 py-3 lg:py-0">
					<div className="lg:flex-1 flex items-center justify-start">
						<span>
							<img
								className="block h-6"
								src={ BrandIcon }
								alt="WC Smart Analytics"
							/>
						</span>
						<div className="h-full ml-4 sm:ml-8 sm:flex gap-y-4 gap-x-8">
							{ menus.map( ( menu, key ) => ( // eslint-disable-line
								<Fragment key={ `?page=${ menu.slug }&path=${ menu.path }` }>
									<button
										onClick={ () => {
											navigateTo( { tab: menu.path } );
										} }
										className={ `${
											activeTab === menu.path
												? 'mb-4 sm:mb-0 border-blogapp text-blogapp active:text-blogapp focus:text-blogapp focus-visible:text-blogapp-hover hover:text-blogapp-hover inline-flex items-center px-1 border-b-2 text-sm leading-[0.875rem] font-medium cursor-pointer wpaib-menu wpaib-active-menu'
												: 'mb-4 sm:mb-0 border-transparent text-slate-600 active:text-blogapp focus-visible:border-slate-300 focus-visible:text-slate-800 hover:border-slate-300 hover:text-slate-800 inline-flex items-center px-1 border-b-2 text-sm leading-[0.875rem] font-medium cursor-pointer wpaib-menu'
										}` }
									>
										{ menu.name }
									</button>
								</Fragment>
							) ) }
						</div>
					</div>

					<div className="absolute bottom-2 lg:inset-y-0 right-0 flex gap-4 items-center sm:static sm:inset-auto ml-auto lg:ml-6 sm:pr-0">
						{ ! wc_sma_admin_data.pro_available && (
							<>
								<div className="text-sm font-medium text-slate-600 border-r hover:text-[#1E293B] hover:svg-hover-color">
									<button
										onClick={ redirectToProPurchase }
										className="inline-flex items-center cursor-pointer text-[#046BD2] hover:text-[#1E293B] focus-visible:text-[#1E293B]"
									>
										<svg
											width="16"
											height="12"
											viewBox="0 0 16 12"
											fill="none"
											xmlns="http://www.w3.org/2000/svg"
											className="mr-2 svg-focusable"
										>
											<path
												d="M3.3335 11.3337H12.6668M1.3335 0.666992L3.3335 8.66699H12.6668L14.6668 0.666992L10.6668 5.33366L8.00016 0.666992L5.3335 5.33366L1.3335 0.666992Z"
												stroke="#046BD2"
												strokeLinecap="round"
												strokeLinejoin="round"
												className="svg-path"
											/>
										</svg>
										{ __( 'Unlock Pro Features', 'suredash' ) }
									</button>
								</div>
								<span className="wpaib-vertical-divider" />
							</>
						) }

						<span className="wpaib-vertical-divider" />

						<div className="flex items-center text-[0.625rem] sm:text-sm font-medium leading-[1.375rem] text-slate-400 divide-x divide-slate-200 gap-2 border-r">
							<CoreVersion />
						</div>
					</div>
				</div>
			</div>
		</section>
	);
}
