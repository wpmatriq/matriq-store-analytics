import React from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardContent } from "@Components/ui/card";
import { ArrowRightFromLine } from "lucide-react";
import { useDispatch } from '@wordpress/data';
import { STORE_NAME } from '@Store/constants';

const topProducts = [
  { name: "Physical Products", tab: 'physical-products-reports', isSoon: false },
  { name: "Variable Products", tab: 'variable-products-reports', isSoon: false },
  { name: "Product Wise", tab: 'product-wise-reports', isSoon: true },
  { name: "Subscription Products", tab: 'subscription-products-reports', isSoon: true },
];

export default function ReportsPage() {
	const { navigateTo } = useDispatch( STORE_NAME );

	return (
		<div className="space-y-6">
			<div>
				<h1 className="text-3xl font-bold m-0 p-0">Reports</h1>
				<p className="text-base text-muted-foreground m-0 p-0">Detailed reports of your store.</p>
			</div>

			{/* Top Products */}
			<div className="grid grid-cols-1">
				<Card>
					<CardContent className="p-6">
						<div className="space-y-4">
							{ topProducts.map((product, index) => (
								product.isSoon ? (
									<div key={index} className="flex items-center justify-between p-3 rounded-lg bg-muted/20 no-underline cursor-not-allowed">
										<div>
											<h3 className="font-medium text-base m-0 p-0">{product.name}</h3>
										</div>
										<div className="text-right">
											<span className='text-xs text-muted-foreground bg-accent px-2 py-1 text-white rounded-full'>
												{ __( 'Soon', 'sales-pulse' )}
											</span>
										</div>
									</div>
								) : (
									<div key={index} className="flex items-center justify-between p-3 rounded-lg bg-muted/20 no-underline cursor-pointer" onClick={ () => { navigateTo( { tab: product.tab } ) } }>
										<div>
											<h3 className="font-medium text-base m-0 p-0">{product.name}</h3>
										</div>
										<div className="text-right">
											<ArrowRightFromLine size={16} />
										</div>
									</div>
								)
							)) }
						</div>
					</CardContent>
				</Card>
			</div>
		</div>
	);
}
