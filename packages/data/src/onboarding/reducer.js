/**
 * Internal dependencies
 */
import TYPES from './action-types';

const onboarding = (
	state = {
		errors: {},
		profileItems: {
			business_extensions: null,
			completed: null,
			industry: null,
			other_platform: null,
			other_platform_name: null,
			product_count: null,
			product_types: null,
			revenue: null,
			selling_venues: null,
			setup_client: null,
			skipped: null,
			theme: null,
			wccom_connected: null,
		},
		requesting: {},
		tasksStatus: {},
	},
	{ type, profileItems, replace, error, isRequesting, selector, tasksStatus }
) => {
	switch ( type ) {
		case TYPES.SET_PROFILE_ITEMS:
			return {
				...state,
				profileItems: replace
					? profileItems
					: { ...state.profileItems, ...profileItems },
			};
		case TYPES.SET_TASKS_STATUS:
			return {
				...state,
				tasksStatus: { ...state.tasksStatus, ...tasksStatus },
			};
		case TYPES.SET_ERROR:
			return {
				...state,
				errors: {
					...state.errors,
					[ selector ]: error,
				},
			};
		case TYPES.SET_IS_REQUESTING:
			return {
				...state,
				requesting: {
					...state.requesting,
					[ selector ]: isRequesting,
				},
			};
		default:
			return state;
	}
};

export default onboarding;
