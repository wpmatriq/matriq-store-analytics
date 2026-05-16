/**
 * Settings Page - tune how Matriq Store Analytics interprets, schedules, and digests.
 *
 * Sections:
 *   1. General - timezone (read-only), currency (read-only).
 *   2. Snapshot schedule - hour:minute for the nightly cron.
 *   3. Email digest - toggle + recipient.
 *   4. Diagnosis sensitivity - Calm / Balanced / Vigilant.
 *
 * Save UX: button stays disabled until a field changes; flashes a Saved pill
 * with a checkmark for ~2s on success. Saved state clears whenever the form
 * drifts from the persisted settings again.
 */
import React, { useEffect, useId, useMemo, useRef, useState } from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';
import {
	AlertTriangle,
	CheckCircle2,
	Clock,
	Globe,
	Mail,
	RefreshCw,
	Save,
	Sparkles,
} from 'lucide-react';
import {
	useSettings,
	useUpdateSettings,
	useSendTestDigest,
} from '@DashboardApp/hooks/useSettings';
import { PageHeader } from '@Components/pulse/PageHeader';
import { InsightCard } from '@Components/pulse/InsightCard';
import { OptionCardGroup } from '@Components/pulse/OptionCard';
import classnames from '@Utils/classnames';
import { SettingSection } from './SettingSection';

const EDITABLE_KEYS = [
	'snapshot_hour',
	'snapshot_min',
	'email_enabled',
	'email_address',
	'diagnosis_sensitivity',
];

const SAVED_BADGE_MS = 2000;

function pickEditable( settings ) {
	if ( ! settings ) {
		return {};
	}
	return EDITABLE_KEYS.reduce( ( acc, key ) => {
		acc[ key ] = settings[ key ];
		return acc;
	}, {} );
}

function formatClock( hour, minute ) {
	const h = Math.max( 0, Math.min( 23, Number( hour ) || 0 ) );
	const m = Math.max( 0, Math.min( 59, Number( minute ) || 0 ) );
	const suffix = h >= 12 ? 'PM' : 'AM';
	const displayHour = h === 0 ? 12 : h > 12 ? h - 12 : h;
	return {
		hh: String( displayHour ).padStart( 2, '0' ),
		mm: String( m ).padStart( 2, '0' ),
		suffix,
	};
}

export default function SettingsPage() {
	const { data: settings, isLoading, error, refetch } = useSettings();
	const updateSettings = useUpdateSettings();

	const [ form, setForm ] = useState( {} );
	const initialRef = useRef( {} );
	const [ showSaved, setShowSaved ] = useState( false );
	const savedTimerRef = useRef( null );

	useEffect( () => {
		if ( settings ) {
			const editable = pickEditable( settings );
			setForm( editable );
			initialRef.current = editable;
		}
	}, [ settings ] );

	useEffect( () => {
		return () => {
			if ( savedTimerRef.current ) {
				clearTimeout( savedTimerRef.current );
			}
		};
	}, [] );

	const isDirty = useMemo( () => {
		return EDITABLE_KEYS.some( ( key ) => {
			const before = initialRef.current?.[ key ];
			const after = form[ key ];
			if ( before === undefined && after === undefined ) {
				return false;
			}
			return String( before ?? '' ) !== String( after ?? '' );
		} );
	}, [ form ] );

	const handleChange = ( key, value ) => {
		setForm( ( prev ) => ( { ...prev, [ key ]: value } ) );
		if ( showSaved ) {
			setShowSaved( false );
		}
	};

	const handleSave = () => {
		if ( ! isDirty || updateSettings.isPending ) {
			return;
		}

		const payload = {
			snapshot_hour: parseInt( form.snapshot_hour, 10 ) || 0,
			snapshot_min: parseInt( form.snapshot_min, 10 ) || 0,
			email_enabled: !! form.email_enabled,
			email_address: form.email_address || '',
			diagnosis_sensitivity: form.diagnosis_sensitivity || 'balanced',
		};

		updateSettings.mutate( payload, {
			onSuccess: ( data ) => {
				const editable = pickEditable( data );
				initialRef.current = editable;
				setForm( editable );
				setShowSaved( true );
				if ( savedTimerRef.current ) {
					clearTimeout( savedTimerRef.current );
				}
				savedTimerRef.current = setTimeout(
					() => setShowSaved( false ),
					SAVED_BADGE_MS
				);
			},
		} );
	};

	if ( isLoading ) {
		return (
			<div className="flex min-h-[400px] items-center justify-center">
				<RefreshCw className="h-6 w-6 animate-spin text-muted-foreground" />
			</div>
		);
	}

	if ( error ) {
		return (
			<InsightCard
				icon={ <AlertTriangle className="h-4 w-4" /> }
				title={ __( 'Could not load settings', 'matriq-store-analytics' ) }
				accent="warning"
			>
				<div className="flex flex-col items-start gap-4">
					<p className="m-0 text-sm text-muted-foreground">
						{ __(
							'We hit an error fetching your settings. Retry, or refresh the page.',
							'matriq-store-analytics'
						) }
					</p>
					<button
						type="button"
						onClick={ () => refetch() }
						className="inline-flex cursor-pointer items-center gap-2 rounded-full border-0 bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-all hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
					>
						{ __( 'Retry', 'matriq-store-analytics' ) }
					</button>
				</div>
			</InsightCard>
		);
	}

	return (
		<motion.div
			initial={ { opacity: 0 } }
			animate={ { opacity: 1 } }
			transition={ { duration: 0.4, ease: [ 0.16, 1, 0.3, 1 ] } }
			className="space-y-6"
		>
			<PageHeader
				eyebrow={ __( 'Configuration', 'matriq-store-analytics' ) }
				title={ __( 'Tune your pulse.', 'matriq-store-analytics' ) }
				subtitle={ __(
					'Configure how Matriq Store Analytics interprets your store data, when it runs, and where it sends digests.',
					'matriq-store-analytics'
				) }
				actions={
					<SaveButton
						isDirty={ isDirty }
						pending={ updateSettings.isPending }
						saved={ showSaved }
						onClick={ handleSave }
					/>
				}
			/>

			<GeneralSection
				settings={ settings }
				delay={ 0 }
			/>
			<ScheduleSection
				form={ form }
				onChange={ handleChange }
				delay={ 0.05 }
			/>
			<EmailSection
				settings={ settings }
				form={ form }
				onChange={ handleChange }
				delay={ 0.1 }
			/>
			<SensitivitySection
				form={ form }
				onChange={ handleChange }
				delay={ 0.15 }
			/>
		</motion.div>
	);
}

function SaveButton( { isDirty, pending, saved, onClick } ) {
	return (
		<div className="flex items-center gap-3">
			<motion.span
				initial={ false }
				animate={
					saved
						? { opacity: 1, x: 0 }
						: { opacity: 0, x: -4 }
				}
				transition={ { duration: 0.3, ease: [ 0.16, 1, 0.3, 1 ] } }
				aria-live="polite"
				className={ classnames(
					'inline-flex items-center gap-1.5 rounded-full border border-solid border-success/30 bg-success/10 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider text-success-foreground/80',
					! saved && 'pointer-events-none'
				) }
			>
				<CheckCircle2 className="h-3 w-3" />
				{ __( 'Saved', 'matriq-store-analytics' ) }
			</motion.span>
			<button
				type="button"
				onClick={ onClick }
				disabled={ ! isDirty || pending }
				className={ classnames(
					'inline-flex cursor-pointer items-center gap-2 rounded-full border-0 bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition-all hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse',
					( ! isDirty || pending ) && 'cursor-not-allowed opacity-60 hover:shadow-sm'
				) }
			>
				{ pending ? (
					<>
						<RefreshCw className="h-4 w-4 animate-spin" />
						{ __( 'Saving…', 'matriq-store-analytics' ) }
					</>
				) : (
					<>
						<Save className="h-4 w-4" />
						{ __( 'Save settings', 'matriq-store-analytics' ) }
					</>
				) }
			</button>
		</div>
	);
}

function GeneralSection( { settings, delay } ) {
	const timezoneLabel = settings?.timezone || '+00:00';
	const currencyLabel = settings?.currency
		? `${ settings.currency }${ settings?.currency_symbol ? ` (${ settings.currency_symbol })` : '' }`
		: '';

	return (
		<SettingSection
			icon={ <Globe className="h-4 w-4" /> }
			title={ __( 'General', 'matriq-store-analytics' ) }
			description={ __( 'Core data interpretation rules.', 'matriq-store-analytics' ) }
			delay={ delay }
		>
			<div className="grid grid-cols-1 gap-6 md:grid-cols-2">
				<ReadOnlyField
					label={ __( 'Timezone', 'matriq-store-analytics' ) }
					value={ timezoneLabel }
					hint={ __( 'Inherited from WordPress settings', 'matriq-store-analytics' ) }
				/>
				<ReadOnlyField
					label={ __( 'Currency', 'matriq-store-analytics' ) }
					value={ currencyLabel }
					hint={ __( 'Inherited from WooCommerce settings', 'matriq-store-analytics' ) }
				/>
			</div>
		</SettingSection>
	);
}

function ReadOnlyField( { label, value, hint } ) {
	return (
		<div className="flex min-w-0 flex-col gap-2">
			<span className="text-sm font-semibold text-foreground">{ label }</span>
			<span className="inline-flex w-fit max-w-full items-center rounded-lg border border-solid border-border bg-muted/60 px-3 py-1.5 font-mono text-sm text-muted-foreground">
				<span className="truncate">{ value || '-' }</span>
			</span>
			{ hint && (
				<p className="m-0 text-xs text-muted-foreground">{ hint }</p>
			) }
		</div>
	);
}

function ScheduleSection( { form, onChange, delay } ) {
	const clock = formatClock( form.snapshot_hour, form.snapshot_min );

	return (
		<SettingSection
			icon={ <Clock className="h-4 w-4" /> }
			title={ __( 'Snapshot schedule', 'matriq-store-analytics' ) }
			description={ __(
				'When the nightly diagnosis runs in your store timezone.',
				'matriq-store-analytics'
			) }
			delay={ delay }
		>
			<div className="flex flex-col items-start justify-between gap-6 rounded-2xl bg-muted/50 md:flex-row md:items-center">
				<div>
					<p className="m-0 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
						{ __( 'Runs daily at', 'matriq-store-analytics' ) }
					</p>
					<p className="m-0 mt-2 font-display text-5xl leading-none text-ink md:text-6xl">
						{ clock.hh }
						<span className="text-pulse">:</span>
						{ clock.mm }
						<span className="ml-2 text-2xl text-muted-foreground">
							{ clock.suffix }
						</span>
					</p>
				</div>
				<div className="grid grid-cols-2 gap-3">
					<NumberInput
						label={ __( 'Hour', 'matriq-store-analytics' ) }
						min={ 0 }
						max={ 23 }
						value={ form.snapshot_hour ?? 2 }
						onChange={ ( v ) => onChange( 'snapshot_hour', v ) }
					/>
					<NumberInput
						label={ __( 'Minute', 'matriq-store-analytics' ) }
						min={ 0 }
						max={ 59 }
						value={ form.snapshot_min ?? 10 }
						onChange={ ( v ) => onChange( 'snapshot_min', v ) }
					/>
				</div>
			</div>
			<p className="m-0 mt-3 text-xs text-muted-foreground">
				{ __(
					"Default 02:10 AM. Pick an off-peak window so snapshot calculations don't compete with checkout traffic.",
					'matriq-store-analytics'
				) }
			</p>
		</SettingSection>
	);
}

function NumberInput( { label, value, min, max, onChange } ) {
	const inputId = useId();
	return (
		<label htmlFor={ inputId } className="flex flex-col items-center gap-1">
			<input
				id={ inputId }
				type="number"
				min={ min }
				max={ max }
				value={ value }
				onChange={ ( e ) => onChange( e.target.value ) }
				className="w-20 rounded-xl border border-solid border-border bg-surface px-3 py-2 text-center font-mono text-xl text-foreground shadow-xs focus-visible:border-pulse focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
			/>
			<span className="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
				{ label }
			</span>
		</label>
	);
}

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function formatRelative( iso ) {
	if ( ! iso ) {
		return '';
	}
	const then = new Date( iso ).getTime();
	if ( Number.isNaN( then ) ) {
		return '';
	}
	const diffSec = Math.max( 0, ( Date.now() - then ) / 1000 );
	if ( diffSec < 60 ) {
		return __( 'just now', 'matriq-store-analytics' );
	}
	if ( diffSec < 3600 ) {
		const m = Math.round( diffSec / 60 );
		return m === 1
			? __( '1 minute ago', 'matriq-store-analytics' )
			// translators: %d: minutes elapsed.
			: `${ m } ${ __( 'minutes ago', 'matriq-store-analytics' ) }`;
	}
	if ( diffSec < 86400 ) {
		const h = Math.round( diffSec / 3600 );
		return h === 1
			? __( '1 hour ago', 'matriq-store-analytics' )
			: `${ h } ${ __( 'hours ago', 'matriq-store-analytics' ) }`;
	}
	const d = Math.round( diffSec / 86400 );
	return d === 1
		? __( '1 day ago', 'matriq-store-analytics' )
		: `${ d } ${ __( 'days ago', 'matriq-store-analytics' ) }`;
}

function formatAbsolute( iso ) {
	if ( ! iso ) {
		return '';
	}
	try {
		return new Date( iso ).toLocaleString();
	} catch ( e ) {
		return iso;
	}
}

function parseDigestError( raw ) {
	if ( ! raw || typeof raw !== 'string' ) {
		return null;
	}
	const sep = raw.indexOf( '|' );
	if ( sep === -1 ) {
		return { iso: null, message: raw };
	}
	return { iso: raw.slice( 0, sep ), message: raw.slice( sep + 1 ) };
}

function EmailSection( { settings, form, onChange, delay } ) {
	const enabled = !! form.email_enabled;
	const recipient = ( form.email_address || '' ).trim();
	const recipientValid = recipient !== '' && EMAIL_RE.test( recipient );
	const canSendTest = enabled && recipientValid;

	const sendTest = useSendTestDigest();
	const [ testFeedback, setTestFeedback ] = useState( null );
	useEffect( () => {
		if ( ! testFeedback ) {
			return undefined;
		}
		const timeout = setTimeout(
			() => setTestFeedback( null ),
			testFeedback.tone === 'error' ? 6000 : 3500
		);
		return () => clearTimeout( timeout );
	}, [ testFeedback ] );

	const lastSentAt = settings?.last_digest_sent_at || null;
	const lastError = parseDigestError( settings?.last_digest_error );

	const handleSendTest = () => {
		if ( ! canSendTest || sendTest.isPending ) {
			return;
		}
		sendTest.mutate( recipient, {
			onSuccess: () => {
				setTestFeedback( {
					tone: 'success',
					text: __( 'Test digest sent.', 'matriq-store-analytics' ),
				} );
			},
			onError: ( err ) => {
				const message =
					err?.message ||
					__( 'Could not send the test digest.', 'matriq-store-analytics' );
				setTestFeedback( { tone: 'error', text: message } );
			},
		} );
	};

	return (
		<SettingSection
			icon={ <Mail className="h-4 w-4" /> }
			title={ __( 'Email digest', 'matriq-store-analytics' ) }
			description={ __(
				'Get the morning briefing delivered straight to your inbox.',
				'matriq-store-analytics'
			) }
			delay={ delay }
		>
			<div className="flex items-center justify-between gap-4 rounded-xl border border-solid border-border bg-surface/60 px-4 py-3">
				<div>
					<p className="m-0 text-sm font-semibold text-foreground">
						{ __( 'Enable email digest', 'matriq-store-analytics' ) }
					</p>
					<p className="m-0 mt-0.5 text-xs text-muted-foreground">
						{ __(
							'Sent each morning after the snapshot completes.',
							'matriq-store-analytics'
						) }
					</p>
				</div>
				<ToggleSwitch
					checked={ enabled }
					onChange={ ( v ) => onChange( 'email_enabled', v ) }
					ariaLabel={ __( 'Enable email digest', 'matriq-store-analytics' ) }
				/>
			</div>

			{ lastError ? (
				<p
					className="m-0 mt-2 text-xs text-destructive"
					title={ lastError.iso ? formatAbsolute( lastError.iso ) : '' }
				>
					{ __( 'Last attempt failed:', 'matriq-store-analytics' ) } { lastError.message }
				</p>
			) : lastSentAt ? (
				<p
					className="m-0 mt-2 text-xs text-muted-foreground"
					title={ formatAbsolute( lastSentAt ) }
				>
					{ __( 'Last sent', 'matriq-store-analytics' ) } { formatRelative( lastSentAt ) }
				</p>
			) : null }

			<div className="mt-4 space-y-2">
				<label
					htmlFor="email-address"
					className="text-sm font-semibold text-foreground"
				>
					{ __( 'Recipient email', 'matriq-store-analytics' ) }
				</label>
				<input
					id="email-address"
					type="email"
					value={ form.email_address || '' }
					disabled={ ! enabled }
					onChange={ ( e ) => onChange( 'email_address', e.target.value ) }
					placeholder={ __( 'admin@yourstore.com', 'matriq-store-analytics' ) }
					className={ classnames(
						'block w-full rounded-xl border border-solid border-border bg-surface px-4 py-2.5 text-sm text-foreground shadow-xs transition-all placeholder:text-muted-foreground focus-visible:border-pulse focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse',
						! enabled && 'cursor-not-allowed bg-muted/60 opacity-60'
					) }
				/>
			</div>

			<div className="mt-4 flex items-center gap-3">
				<button
					type="button"
					onClick={ handleSendTest }
					disabled={ ! canSendTest || sendTest.isPending }
					className={ classnames(
						'inline-flex items-center gap-2 rounded-full border border-solid border-border bg-surface px-4 py-2 text-xs font-semibold text-foreground transition-all hover:border-border/80 hover:bg-surface-elevated focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse',
						( ! canSendTest || sendTest.isPending ) &&
							'cursor-not-allowed opacity-60 hover:bg-surface'
					) }
				>
					<Mail className="h-3.5 w-3.5" />
					{ sendTest.isPending
						? __( 'Sending…', 'matriq-store-analytics' )
						: __( 'Send test digest', 'matriq-store-analytics' ) }
				</button>
				{ testFeedback ? (
					<span
						className={ classnames(
							'text-xs',
							testFeedback.tone === 'success'
								? 'text-success'
								: 'text-destructive'
						) }
					>
						{ testFeedback.text }
					</span>
				) : null }
			</div>
		</SettingSection>
	);
}

function ToggleSwitch( { checked, onChange, ariaLabel } ) {
	return (
		<button
			type="button"
			role="switch"
			aria-checked={ checked }
			aria-label={ ariaLabel }
			onClick={ () => onChange( ! checked ) }
			className={ classnames(
				'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-0 shadow-inner ring-1 ring-inset ring-border/60 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse p-1',
				checked ? 'bg-success' : 'bg-foreground/15'
			) }
		>
			<motion.span
				layout
				transition={ { type: 'spring', stiffness: 520, damping: 32 } }
				className={ classnames(
					'inline-block h-4 w-4 rounded-full bg-white shadow-sm ring-1 ring-foreground/5',
					checked ? 'ml-[20px]' : ''
				) }
			/>
		</button>
	);
}

function SensitivitySection( { form, onChange, delay } ) {
	const options = [
		{
			value: 'calm',
			title: __( 'Calm', 'matriq-store-analytics' ),
			description: __( 'Only flag major shifts', 'matriq-store-analytics' ),
		},
		{
			value: 'balanced',
			title: __( 'Balanced', 'matriq-store-analytics' ),
			description: __( 'Recommended default', 'matriq-store-analytics' ),
		},
		{
			value: 'vigilant',
			title: __( 'Vigilant', 'matriq-store-analytics' ),
			description: __( 'Surface every anomaly', 'matriq-store-analytics' ),
		},
	];

	return (
		<SettingSection
			icon={ <Sparkles className="h-4 w-4" /> }
			title={ __( 'Diagnosis sensitivity', 'matriq-store-analytics' ) }
			description={ __(
				'How aggressively Matriq Store Analytics flags anomalies.',
				'matriq-store-analytics'
			) }
			delay={ delay }
		>
			<OptionCardGroup
				label={ __( 'Diagnosis sensitivity', 'matriq-store-analytics' ) }
				value={ form.diagnosis_sensitivity || 'balanced' }
				onChange={ ( v ) => onChange( 'diagnosis_sensitivity', v ) }
				options={ options }
				gridClassName="grid grid-cols-1 gap-3 md:grid-cols-3"
			/>
		</SettingSection>
	);
}
