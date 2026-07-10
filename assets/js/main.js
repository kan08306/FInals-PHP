// DOM Element References
const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.main-nav');
const pillMenus = document.querySelectorAll('.pill-menu');
const locationLabel = document.querySelector('#currentLocationLabel');
const locationModeButtons = document.querySelectorAll('[data-location-mode]');
const locationChoiceButtons = document.querySelectorAll('[data-location-choice]');
const locationSelect = document.querySelector('[data-location-select]');
const locationSelectToggle = document.querySelector('.location-select-toggle');
const countryOptions = document.querySelectorAll('[data-country-option]');
const filterButtons = document.querySelectorAll('[data-filter]');
const eventCards = document.querySelectorAll('[data-event-filter]');
const clickableEventCards = document.querySelectorAll('[data-event-href]');
const eventGrids = document.querySelectorAll('.event-grid');
const destinationTrack = document.querySelector('[data-carousel-track]');
const carouselPreviousButton = document.querySelector('[data-carousel-prev]');
const carouselNextButton = document.querySelector('[data-carousel-next]');
const passwordToggle = document.querySelector('#toggle-password, [data-password-toggle]');
const profileMenu = document.querySelector('[data-profile-menu]');
const profileToggle = document.querySelector('[data-profile-toggle]');
const landingBrowseEventsButton = document.querySelector('[data-landing-browse-events]');
const landingEventsSection = document.querySelector('#events');
const categoryButtons = document.querySelectorAll('[data-category-filter]');
const browseLocationLabel = document.querySelector('[data-browse-location]');
const eventWizard = document.querySelector('[data-event-wizard]');
const wizardPanels = document.querySelectorAll('[data-step-panel]');
const wizardStepItems = document.querySelectorAll('[data-step-nav]');
const wizardNextButtons = document.querySelectorAll('[data-next-step]');
const createEventButton = document.querySelector('[data-create-event]');
const sidebarEventTitle = document.querySelector('[data-sidebar-title]');
const sidebarEventDate = document.querySelector('[data-sidebar-date]');
const sidebarEventTime = document.querySelector('[data-sidebar-time]');
const eventDatePreview = document.querySelector('[data-event-date-preview]');
const bannerInput = document.querySelector('[data-banner-input]');
const bannerDropzone = document.querySelector('[data-banner-dropzone]');
const bannerPreview = document.querySelector('[data-banner-preview]');
const summaryBannerImage = document.querySelector('[data-summary-banner-image]');
const summaryBannerPlaceholder = document.querySelector('[data-summary-banner-placeholder]');
const visibilityOptions = document.querySelectorAll('[data-visibility-option]');
const scheduleOptions = document.querySelectorAll('[data-schedule-option]');
const privateAudienceWrap = document.querySelector('[data-private-audience-wrap]');
const privateAudienceSelect = document.querySelector('[data-private-audience-select]') || document.querySelector('[data-private-audience]');
const publicAudienceDisplay = document.querySelector('[data-public-audience-display]');
const visibilityLabel = document.querySelector('[data-visibility-label]');
const visibilityHelp = document.querySelector('[data-visibility-help]');
const visibilityValue = document.querySelector('[data-visibility-value]');
const scheduleWrap = document.querySelector('[data-schedule-wrap]');
const publishDateInput = document.querySelector('[data-publish-date]');
const publishTimeInput = document.querySelector('[data-publish-time]');
const locationTypeButtons = document.querySelectorAll('[data-location-type-option]');
const venueLocationPanel = document.querySelector('[data-venue-location-panel]');
const onlineLocationPanel = document.querySelector('[data-online-location-panel]');
const tbaLocationPanel = document.querySelector('[data-tba-location-panel]');
const mapPreviewPanel = document.querySelector('[data-map-preview]');
const venueLocationFields = document.querySelectorAll('[data-event-venue], [data-event-country], [data-event-city], [data-event-address]');
const eventCountrySelect = document.querySelector('[data-event-country]');
const eventCitySelect = document.querySelector('[data-event-city]');
const countryCityOptionsSource = document.querySelector('[data-country-city-options]');
const onlineLocationFields = document.querySelectorAll('[data-event-online-link], [data-event-platform]');
const onlinePlatformSelect = document.querySelector('[data-event-platform]');
const onlinePlatformOther = document.querySelector('[data-event-platform-other]');
const dashboardFilterButtons = document.querySelectorAll('[data-dashboard-filter]');
const dashboardEventRows = document.querySelectorAll('[data-dashboard-event-row]');
const adminRows = document.querySelectorAll('[data-admin-row]');
const adminSearchInput = document.querySelector('[data-admin-search]');
const adminSortSelect = document.querySelector('[data-admin-sort]');
const adminFilterButtons = document.querySelectorAll('[data-admin-filter]');
let adminPageButtons = document.querySelectorAll('[data-admin-page]');
const adminPreviousButton = document.querySelector('[data-admin-page-previous]');
const adminNextButton = document.querySelector('[data-admin-page-next]');
const adminDetailModal = document.querySelector('[data-admin-detail-modal]');
const adminEditModal = document.querySelector('[data-admin-edit-modal]');
const adminUserEditModal = document.querySelector('[data-admin-user-edit-modal]');
const adminMessageModal = document.querySelector('[data-admin-message-modal]');
const reviewSelectButtons = document.querySelectorAll('[data-review-select]');
const revisionModal = document.querySelector('[data-revision-modal]');
const revisionForm = document.querySelector('[data-revision-form]');
const approvalHistory = document.querySelector('[data-approval-history]');
const authRequiredRegisterButtons = document.querySelectorAll('[data-auth-required-register]');
const authRequiredLikeButtons = document.querySelectorAll('[data-auth-required-like]');
const authRequiredCityLinks = document.querySelectorAll('[data-auth-required-city]');
const eventRegisterButtons = document.querySelectorAll('[data-event-register]');
const authRequiredModal = document.querySelector('[data-auth-required-modal]');
const authRequiredTitle = document.querySelector('[data-auth-required-title]');
const authRequiredMessage = document.querySelector('[data-auth-required-message]');
const registrationModal = document.querySelector('[data-registration-modal]');
const privateEventOpenButtons = document.querySelectorAll('[data-private-event-open]');
const privateEventModal = document.querySelector('[data-private-event-modal]');
const modalCloseButtons = document.querySelectorAll('[data-modal-close]');
const registrationForm = document.querySelector('[data-registration-form]');
const registrationSuccess = document.querySelector('[data-registration-success]');
const registrationConfirm = document.querySelector('[data-registration-confirm]');
const registrationSubmit = document.querySelector('[data-registration-submit]');
const registrationTitle = document.querySelector('[data-registration-title]');
const registrationDate = document.querySelector('[data-registration-date]');
const registrationTime = document.querySelector('[data-registration-time]');
const registrationLocation = document.querySelector('[data-registration-location]');
const registrationEventId = document.querySelector('input[data-registration-event-id]');
const allowedBannerTypes = ['image/jpeg', 'image/png', 'image/webp'];
const allowedBannerFilePattern = /\.(jpe?g|png|webp)$/i;
// Shared Interface State
let activeEventFilter = 'all';
let activeCategoryFilter = 'all';
let activeLocationFilter = document.querySelector('[data-location-filter].active')?.getAttribute('data-location-filter') || 'all';
let activeTicketStatusFilter = document.querySelector('[data-ticket-status-filter].active')?.getAttribute('data-ticket-status-filter') || 'all';
let activeDashboardFilter = 'all';
let activeAdminFilter = 'all';
let currentAdminPage = 1;
let activeModal = null;
let activeAdminEventRow = null;
let activeAdminUserRow = null;
const adminPageSize = 5;
// Event Wizard State
const eventWizardState = {
    currentStep: Number(eventWizard?.getAttribute('data-initial-step') || 1),
    completedSteps: new Set(),
    bannerSrc: '',
    existingEvent: eventWizard?.hasAttribute('data-existing-event') || false,
    locationLocked: eventWizard?.hasAttribute('data-location-locked') || false,
};
let activeCountryFilter = '';
let countryCityOptions = {};

// Country And City Options
if (countryCityOptionsSource) {
    try {
        countryCityOptions = JSON.parse(countryCityOptionsSource.textContent || '{}');
    } catch (error) {
        countryCityOptions = {};
    }
}

// Togglepassword
function togglePassword() {
    const field = document.getElementById('password-field') || document.getElementById('passwordInput');
    const icon = document.getElementById('eye-icon') || document.getElementById('passwordEyeIcon');

    if (!field || !icon) {
        return;
    }

    const isHidden = field.type === 'password';
    field.type = isHidden ? 'text' : 'password';
    passwordToggle?.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');

    icon.innerHTML = isHidden
        ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>'
        : '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
}

// Moveindicator
function moveIndicator(menu, item) {
    if (!menu || !item) {
        return;
    }

    const menuBox = menu.getBoundingClientRect();
    const itemBox = item.getBoundingClientRect();
    const menuStyles = window.getComputedStyle(menu);
    const edgeGap = Number.parseFloat(menuStyles.getPropertyValue('--pill-edge-gap'))
        || Number.parseFloat(menuStyles.paddingLeft)
        || 4;
    const rawLeft = itemBox.left - menuBox.left;
    const maxLeft = Math.max(edgeGap, menuBox.width - itemBox.width - edgeGap);
    const indicatorLeft = Math.min(Math.max(rawLeft, edgeGap), maxLeft);

    menu.style.setProperty('--indicator-left', `${Math.round(indicatorLeft)}px`);
    menu.style.setProperty('--indicator-width', `${Math.round(itemBox.width)}px`);
    menu.style.setProperty('--indicator-height', `${Math.round(itemBox.height)}px`);
}

// Updatelocation
function updateLocation(label) {
    if (locationLabel) {
        locationLabel.textContent = label;
    }

    if (browseLocationLabel) {
        browseLocationLabel.textContent = label.replace(' Events', '');
    }
}

// Eventmatchesactivefilters
function eventMatchesActiveFilters(card) {
    const cardFilter = card.getAttribute('data-event-filter');
    const cardCategory = card.getAttribute('data-event-category');
    const cardLocationType = card.getAttribute('data-event-location-type');
    const cardCountry = card.getAttribute('data-event-country');
    const cardRegistrationStatus = card.getAttribute('data-registration-status');
    const matchesFilter = activeEventFilter === 'all' || cardFilter === activeEventFilter;
    const matchesCategory = activeCategoryFilter === 'all' || !cardCategory || cardCategory === activeCategoryFilter;
    const matchesLocation = activeLocationFilter === 'all' || !cardLocationType || cardLocationType === activeLocationFilter;
    const matchesCountry = activeCountryFilter === '' || !cardCountry || cardCountry === activeCountryFilter;
    const matchesTicketStatus = activeTicketStatusFilter === 'all' || !cardRegistrationStatus || cardRegistrationStatus === activeTicketStatusFilter;

    return matchesFilter && matchesCategory && matchesLocation && matchesCountry && matchesTicketStatus;
}

// Filterevents
function filterEvents() {
    eventGrids.forEach((grid) => {
        const cards = Array.from(grid.querySelectorAll('[data-event-filter]'));
        const matchingCards = cards.filter(eventMatchesActiveFilters);
        const emptyState = grid.parentElement?.querySelector('[data-filter-empty]');

        cards.forEach((card) => {
            card.classList.toggle('is-hidden', !matchingCards.includes(card));
        });

        if (emptyState) {
            emptyState.hidden = matchingCards.length > 0;
        }
    });
}

// Closelocationselect
function closeLocationSelect() {
    if (!locationSelect || !locationSelectToggle) {
        return;
    }

    locationSelect.classList.remove('open');
    locationSelectToggle.setAttribute('aria-expanded', 'false');
}

// Getcarouselstep
function getCarouselStep() {
    if (!destinationTrack) {
        return 0;
    }

    const firstCard = destinationTrack.querySelector('.destination-card');

    if (!firstCard) {
        return 0;
    }

    const trackStyles = window.getComputedStyle(destinationTrack);
    const gap = parseFloat(trackStyles.columnGap || trackStyles.gap) || 0;

    return firstCard.offsetWidth + gap;
}

// Updatecarouselbuttons
function updateCarouselButtons() {
    if (!destinationTrack || !carouselPreviousButton || !carouselNextButton) {
        return;
    }

    const maxScrollLeft = destinationTrack.scrollWidth - destinationTrack.clientWidth;

    carouselPreviousButton.disabled = destinationTrack.scrollLeft <= 2;
    carouselNextButton.disabled = destinationTrack.scrollLeft >= maxScrollLeft - 2;
}

// Scrolldestinations
function scrollDestinations(direction) {
    if (!destinationTrack) {
        return;
    }

    destinationTrack.scrollBy({
        left: getCarouselStep() * direction,
        behavior: 'smooth',
    });
}

// Getwizardfield
function getWizardField(selector) {
    return eventWizard?.querySelector(selector);
}

// Getwizardvalue
function getWizardValue(selector) {
    return getWizardField(selector)?.value.trim() || '';
}

// Getcitiesforcountry
function getCitiesForCountry(country) {
    const countryName = country || 'Philippines';

    if (Array.isArray(countryCityOptions[countryName]) && countryCityOptions[countryName].length) {
        return countryCityOptions[countryName];
    }

    return [
        `${countryName} City`,
        `${countryName} Central`,
        `${countryName} North`,
        `${countryName} South`,
        `${countryName} Business District`,
    ];
}

// Updatecityoptionsforcountry
function updateCityOptionsForCountry(keepSelectedCity = false) {
    if (!eventCountrySelect || !eventCitySelect) {
        return;
    }

    const selectedCountry = eventCountrySelect.value || 'Philippines';
    const previousCity = keepSelectedCity
        ? (eventCitySelect.dataset.selectedCity || eventCitySelect.value)
        : '';
    const cityOptions = getCitiesForCountry(selectedCountry);
    const selectedCity = previousCity && cityOptions.includes(previousCity)
        ? previousCity
        : cityOptions[0];

    eventCitySelect.innerHTML = '';

    cityOptions.forEach((city) => {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        option.selected = city === selectedCity;
        eventCitySelect.appendChild(option);
    });

    eventCitySelect.dataset.selectedCity = selectedCity;
}

// Setwizarderror
function setWizardError(step, message) {
    const errorBox = document.querySelector(`[data-step-error="${step}"]`);

    if (errorBox) {
        errorBox.textContent = message;
    }
}

// Normalizeeventdateinput
function normalizeEventDateInput(value) {
    const digits = value.replace(/\D/g, '').slice(0, 8);
    const month = digits.slice(0, 2);
    const day = digits.slice(2, 4);
    const year = digits.slice(4, 8);

    if (digits.length > 4) {
        return `${month}/${day}/${year}`;
    }

    if (digits.length > 2) {
        return `${month}/${day}`;
    }

    return month;
}

// Parseeventdateinput
function parseEventDateInput(value) {
    const match = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);

    if (!match) {
        return null;
    }

    const month = Number(match[1]);
    const day = Number(match[2]);
    const year = Number(match[3]);
    const parsedDate = new Date(year, month - 1, day);

    if (
        parsedDate.getFullYear() !== year
        || parsedDate.getMonth() !== month - 1
        || parsedDate.getDate() !== day
    ) {
        return null;
    }

    return parsedDate;
}

// Formatreadableeventdate
function formatReadableEventDate(value) {
    const parsedDate = parseEventDateInput(value);

    if (!parsedDate) {
        return '';
    }

    const monthName = parsedDate.toLocaleDateString('en-US', { month: 'long' });
    const weekdayName = parsedDate.toLocaleDateString('en-US', { weekday: 'long' });

    return `${monthName} ${parsedDate.getDate()}, ${parsedDate.getFullYear()}, ${weekdayName}`;
}

// Formatwizardtime
function formatWizardTime(value) {
    if (!value) {
        return '';
    }

    const timeParts = value.split(':');
    const selectedTime = new Date();

    selectedTime.setHours(Number(timeParts[0] || 0), Number(timeParts[1] || 0), 0, 0);

    if (Number.isNaN(selectedTime.getTime())) {
        return value;
    }

    return selectedTime.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
    });
}

// Buildeventdatetext
function buildEventDateText() {
    const eventDate = formatReadableEventDate(getWizardValue('[data-event-date]'));

    if (eventDate) {
        return eventDate;
    }

    return 'Event Date';
}

// Buildeventtimetext
function buildEventTimeText() {
    const startTime = formatWizardTime(getWizardValue('[data-event-start-time]'));
    const endTime = formatWizardTime(getWizardValue('[data-event-end-time]'));

    if (startTime && endTime) {
        return `${startTime} - ${endTime}`;
    }

    return startTime || endTime || '';
}

// Buildeventlocationtext
function buildEventLocationText() {
    const locationType = getWizardValue('[data-event-location]');
    const onlineLink = getWizardValue('[data-event-online-link]');
    const platform = getWizardValue('[data-event-platform]');
    const platformOther = getWizardValue('[data-event-platform-other]');
    const venue = getWizardValue('[data-event-venue]');
    const address = getWizardValue('[data-event-address]');
    const city = getWizardValue('[data-event-city]');
    const country = getWizardValue('[data-event-country]');

    if (locationType === 'Online event') {
        const platformText = platform === 'Other' ? platformOther : platform;
        const onlineParts = [platformText, onlineLink].filter(Boolean);

        return onlineParts.length ? onlineParts.join(' - ') : 'Online event';
    }

    if (locationType === 'To be announced') {
        return 'Location to be announced';
    }

    const locationParts = [venue, address, city, country].filter(Boolean);

    if (locationParts.length) {
        return locationParts.join(', ');
    }

    return locationType || 'Address of the event';
}

// Updateonlineplatformother
function updateOnlinePlatformOther() {
    const showOtherField = onlinePlatformSelect?.value === 'Other';
    const locationLocked = eventWizardState.locationLocked;

    if (onlinePlatformOther) {
        onlinePlatformOther.hidden = !showOtherField;
        onlinePlatformOther.disabled = locationLocked || !showOtherField;
        onlinePlatformOther.required = Boolean(!locationLocked && showOtherField && onlineLocationPanel && !onlineLocationPanel.hidden);
        onlinePlatformOther.classList.remove('is-invalid');
    }
}

// Updatelocationtypefields
function updateLocationTypeFields() {
    const selectedType = getWizardValue('[data-event-location]') || 'Venue';
    const showVenue = selectedType === 'Venue';
    const showOnline = selectedType === 'Online event';
    const showTba = selectedType === 'To be announced';
    const locationLocked = eventWizardState.locationLocked;

    if (venueLocationPanel) {
        venueLocationPanel.hidden = !showVenue;
    }

    if (onlineLocationPanel) {
        onlineLocationPanel.hidden = !showOnline;
    }

    if (tbaLocationPanel) {
        tbaLocationPanel.hidden = !showTba;
    }

    if (mapPreviewPanel) {
        mapPreviewPanel.hidden = !showVenue;
    }

    venueLocationFields.forEach((field) => {
        field.required = !locationLocked && showVenue;
        field.disabled = locationLocked || !showVenue;
        field.classList.remove('is-invalid');
    });

    onlineLocationFields.forEach((field) => {
        field.required = !locationLocked && showOnline;
        field.disabled = locationLocked || !showOnline;
        field.classList.remove('is-invalid');
    });

    updateOnlinePlatformOther();
    updateMapPreview();
    updatePublishSummary();
}

// Updatemappreview
function updateMapPreview() {
    const mapTitle = document.querySelector('[data-map-preview-title]');
    const mapLocation = document.querySelector('[data-map-preview-location]');
    const mapLink = document.querySelector('[data-map-preview-link]');
    const venue = getWizardValue('[data-event-venue]');
    const address = getWizardValue('[data-event-address]');
    const city = getWizardValue('[data-event-city]');
    const country = getWizardValue('[data-event-country]');
    const locationText = [address, city, country].filter(Boolean).join(', ');
    const searchText = [venue, address, city, country].filter(Boolean).join(', ');

    if (mapTitle) {
        mapTitle.textContent = venue || 'Venue preview';
    }

    if (mapLocation) {
        mapLocation.textContent = locationText || 'Select a venue, city, and country';
    }

    if (mapLink) {
        mapLink.href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(searchText || 'Philippines')}`;
    }
}

// Updatepublishsummary
function updatePublishSummary() {
    const summaryTitle = document.querySelector('[data-summary-title]');
    const summaryCategory = document.querySelector('[data-summary-category]');
    const summaryDate = document.querySelector('[data-summary-date]');
    const summaryLocation = document.querySelector('[data-summary-location]');
    const summaryDescription = document.querySelector('[data-summary-description]');
    const eventTitle = getWizardValue('[data-event-name]');
    const eventSummary = getWizardValue('[data-event-summary]');
    const eventDate = buildEventDateText();
    const eventTime = buildEventTimeText();
    const hasEventDate = eventDate !== 'Event Date';
    const summaryDateText = hasEventDate
        ? (eventTime ? `${eventDate}, ${eventTime}` : eventDate)
        : 'Date and time of the event';

    if (summaryTitle) {
        summaryTitle.textContent = eventTitle || 'Event Title';
    }

    if (summaryCategory) {
        summaryCategory.textContent = getWizardValue('[data-event-type]') || 'Not selected';
    }

    if (summaryDate) {
        summaryDate.textContent = summaryDateText;
    }

    if (summaryLocation) {
        summaryLocation.textContent = buildEventLocationText();
    }

    if (summaryDescription) {
        summaryDescription.textContent = eventTitle && eventSummary
            ? `${eventTitle} - ${eventSummary}`
            : 'Event Title - Summary of the Event';
    }

    if (sidebarEventTitle) {
        sidebarEventTitle.textContent = eventTitle || 'Event Title';
    }

    if (sidebarEventDate) {
        sidebarEventDate.textContent = eventDate;
    }

    if (sidebarEventTime) {
        sidebarEventTime.textContent = eventTime || 'Event Time';
    }

    if (eventDatePreview) {
        eventDatePreview.textContent = !hasEventDate
            ? 'Enter MM/DD/YYYY to preview the full event date.'
            : eventDate;
    }
}

// Updatewizardstep
function updateWizardStep(step) {
    if (!eventWizard) {
        return;
    }

    eventWizardState.currentStep = step;

    wizardPanels.forEach((panel) => {
        panel.hidden = Number(panel.getAttribute('data-step-panel')) !== step;
    });

    wizardStepItems.forEach((item) => {
        const itemStep = Number(item.getAttribute('data-step-nav'));
        const isActive = itemStep === step;
        const isCompleted = eventWizardState.completedSteps.has(itemStep);
        const stateLabel = item.querySelector('[data-step-state]');

        item.classList.toggle('active', isActive);
        item.classList.toggle('completed', isCompleted);

        if (stateLabel) {
            stateLabel.textContent = isActive ? 'Current' : (isCompleted ? 'Completed' : 'Pending');
        }
    });

    updateMapPreview();
    updatePublishSummary();
}

// Validatewizardstep
function validateWizardStep(step) {
    const panel = document.querySelector(`[data-step-panel="${step}"]`);

    if (!panel) {
        return true;
    }

    let isValid = true;
    const requiredFields = panel.querySelectorAll('[required]');

    setWizardError(step, '');

    requiredFields.forEach((field) => {
        if (field.disabled) {
            return;
        }

        const fieldIsEmpty = !field.value.trim();

        field.classList.toggle('is-invalid', fieldIsEmpty);

        if (fieldIsEmpty) {
            isValid = false;
        }
    });

    if (step === 1) {
        const eventDate = getWizardField('[data-event-date]');
        const splitStartTime = getWizardField('[data-event-start-time]');
        const splitEndTime = getWizardField('[data-event-end-time]');
        const capacity = getWizardField('[data-event-capacity]');

        if (eventDate?.value && !parseEventDateInput(eventDate.value.trim())) {
            eventDate.classList.add('is-invalid');
            setWizardError(step, 'Use a valid event date in MM/DD/YYYY format.');
            return false;
        }

        if (splitStartTime?.value && splitEndTime?.value && splitStartTime.value >= splitEndTime.value) {
            splitStartTime.classList.add('is-invalid');
            splitEndTime.classList.add('is-invalid');
            setWizardError(step, 'Event end time must be after the start time.');
            return false;
        }

        if (capacity?.value && Number(capacity.value) < 1) {
            capacity.classList.add('is-invalid');
            setWizardError(step, 'Event capacity must be at least 1.');
            return false;
        }
    }

    if (step === 2 && !eventWizardState.existingEvent) {
        const hasBannerFile = Boolean(bannerInput?.files && bannerInput.files.length > 0);

        if (!hasBannerFile && !eventWizardState.bannerSrc) {
            bannerInput?.classList.add('is-invalid');
            bannerDropzone?.classList.add('is-invalid');
            setWizardError(step, 'Please upload an event banner image before continuing.');
            return false;
        }
    }

    if (step === 3) {
        const scheduleLater = document.querySelector('[name="publish_schedule"][value="later"]')?.checked;

        if (scheduleLater && !publishDateInput?.value.trim()) {
            publishDateInput?.classList.add('is-invalid');
            isValid = false;
        }

        if (scheduleLater && !publishTimeInput?.value.trim()) {
            publishTimeInput?.classList.add('is-invalid');
            isValid = false;
        }
    }

    if (!isValid) {
        setWizardError(step, 'Please complete the required fields before continuing.');
    }

    return isValid;
}

// Updatepublishcontrols
function updatePublishControls() {
    const visibilityToggle = document.querySelector('[data-visibility-option]');
    const privateSelected = visibilityToggle?.checked;
    const scheduleLater = document.querySelector('[name="publish_schedule"][value="later"]')?.checked;

    if (privateAudienceWrap) {
        privateAudienceWrap.hidden = !privateSelected;
    }

    if (privateAudienceSelect) {
        privateAudienceSelect.required = Boolean(privateSelected);
        privateAudienceSelect.disabled = !privateSelected;
        privateAudienceSelect.classList.remove('is-invalid');
    }

    if (publicAudienceDisplay) {
        publicAudienceDisplay.hidden = Boolean(privateSelected);
    }

    if (visibilityLabel) {
        visibilityLabel.textContent = privateSelected ? 'Private Event' : 'Public Event';
    }

    if (visibilityHelp) {
        visibilityHelp.textContent = privateSelected
            ? 'Only available to a selected audience'
            : 'Visible to everyone';
    }

    if (visibilityValue) {
        visibilityValue.value = privateSelected ? 'private' : 'public';
    }

    if (scheduleWrap) {
        scheduleWrap.hidden = false;
    }

    if (publishDateInput) {
        publishDateInput.required = Boolean(scheduleLater);
        publishDateInput.disabled = !scheduleLater;
        publishDateInput.classList.remove('is-invalid');
    }

    if (publishTimeInput) {
        publishTimeInput.required = Boolean(scheduleLater);
        publishTimeInput.disabled = !scheduleLater;
        publishTimeInput.classList.remove('is-invalid');
    }
}

// Getmatchingdashboardrows
function getMatchingDashboardRows() {
    return Array.from(dashboardEventRows).filter((row) => {
        const rowStatus = row.getAttribute('data-dashboard-status');

        return activeDashboardFilter === 'all' || rowStatus === activeDashboardFilter;
    });
}

// Filterdashboardrows
function filterDashboardRows() {
    if (!dashboardEventRows.length) {
        return;
    }

    const matchingRows = getMatchingDashboardRows();

    dashboardEventRows.forEach((row) => {
        row.hidden = !matchingRows.includes(row);
    });
}

// Getadminrowtext
function getAdminRowText(row) {
    return row.getAttribute('data-admin-search-text') || row.textContent.toLowerCase();
}

// Getmatchingadminrows
function getMatchingAdminRows() {
    const searchValue = adminSearchInput?.value.trim().toLowerCase() || '';

    return Array.from(adminRows).filter((row) => {
        const rowStatus = row.getAttribute('data-admin-status');
        const rowVisibility = row.getAttribute('data-admin-visibility');
        const approvedStatuses = ['approved', 'published', 'open'];
        const rejectedStatuses = ['rejected', 'closed'];
        let matchesFilter = activeAdminFilter === 'all';

        if (activeAdminFilter === 'approved') {
            matchesFilter = approvedStatuses.includes(rowStatus);
        } else if (activeAdminFilter === 'rejected') {
            matchesFilter = rejectedStatuses.includes(rowStatus);
        } else if (activeAdminFilter !== 'all') {
            matchesFilter = rowStatus === activeAdminFilter || rowVisibility === activeAdminFilter;
        }

        const matchesSearch = !searchValue || getAdminRowText(row).includes(searchValue);

        return matchesFilter && matchesSearch;
    });
}

// Syncadminpagebuttons
function syncAdminPageButtons(totalPages) {
    const paginationMenu = document.querySelector('[data-admin-pagination]');

    if (!paginationMenu) {
        return;
    }

    const nextButton = paginationMenu.querySelector('[data-admin-page-next]');

    for (let page = 1; page <= totalPages; page += 1) {
        if (!paginationMenu.querySelector(`[data-admin-page="${page}"]`)) {
            const button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('data-admin-page', String(page));
            button.textContent = String(page);
            paginationMenu.insertBefore(button, nextButton);
        }
    }

    paginationMenu.querySelectorAll('[data-admin-page]').forEach((button) => {
        const page = Number(button.getAttribute('data-admin-page'));

        if (page > totalPages) {
            button.remove();
        }
    });

    adminPageButtons = document.querySelectorAll('[data-admin-page]');
}

// Updateadminpagination
function updateAdminPagination(totalRows) {
    const paginationMenu = document.querySelector('[data-admin-pagination]');

    if (!paginationMenu || !adminPageButtons.length) {
        return;
    }

    const totalPages = Math.max(1, Math.ceil(totalRows / adminPageSize));
    paginationMenu.hidden = totalPages <= 1;

    if (totalPages <= 1) {
        currentAdminPage = 1;
        return;
    }

    syncAdminPageButtons(totalPages);

    if (currentAdminPage > totalPages) {
        currentAdminPage = totalPages;
    }

    adminPageButtons.forEach((button) => {
        const page = Number(button.getAttribute('data-admin-page'));

        button.hidden = page > totalPages;
        button.classList.toggle('active', page === currentAdminPage);
    });

    if (adminPreviousButton) {
        adminPreviousButton.hidden = currentAdminPage === 1;
        adminPreviousButton.classList.remove('active');
    }

    if (adminNextButton) {
        adminNextButton.hidden = currentAdminPage >= totalPages;
        adminNextButton.disabled = currentAdminPage >= totalPages;
        adminNextButton.classList.remove('active');
    }

    moveIndicator(paginationMenu, paginationMenu?.querySelector('.active'));
}

// Sortadminrows
function sortAdminRows() {
    if (!adminRows.length || !adminSortSelect) {
        return;
    }

    const tbody = adminRows[0].parentElement;
    const sortType = adminSortSelect.value;
    const sortedRows = Array.from(adminRows).sort((firstRow, secondRow) => {
        if (sortType === 'date') {
            return new Date(secondRow.getAttribute('data-admin-date')) - new Date(firstRow.getAttribute('data-admin-date'));
        }

        if (sortType === 'status') {
            return (firstRow.getAttribute('data-admin-status-label') || '').localeCompare(secondRow.getAttribute('data-admin-status-label') || '');
        }

        return (firstRow.getAttribute('data-admin-name') || '').localeCompare(secondRow.getAttribute('data-admin-name') || '');
    });

    sortedRows.forEach((row) => tbody.appendChild(row));
}

// Filteradminrows
function filterAdminRows() {
    if (!adminRows.length) {
        return;
    }

    const matchingRows = getMatchingAdminRows();
    const startIndex = (currentAdminPage - 1) * adminPageSize;
    const endIndex = startIndex + adminPageSize;

    adminRows.forEach((row) => {
        const matchedIndex = matchingRows.indexOf(row);

        row.hidden = !(matchedIndex >= startIndex && matchedIndex < endIndex);
    });

    updateAdminPagination(matchingRows.length);
}

// Settext
function setText(selector, value) {
    const elements = document.querySelectorAll(selector);

    elements.forEach((element) => {
        element.textContent = value || '';
    });
}

// Setformvalue
function setFormValue(selector, value) {
    const element = document.querySelector(selector);

    if (element) {
        element.value = value || '';
    }
}

// Setformvalues
function setFormValues(selector, value) {
    document.querySelectorAll(selector).forEach((element) => {
        element.value = value || '';
    });
}

// Populateuserdetail
function populateUserDetail(row) {
    activeAdminUserRow = row;

    const status = row.getAttribute('data-admin-status') || 'active';
    const statusLabel = row.getAttribute('data-admin-status-label') || 'Active';
    const roleLabel = row.getAttribute('data-admin-type-label') || row.getAttribute('data-admin-type') || 'Participant';
    const avatarSrc = row.getAttribute('data-admin-avatar-src') || '';
    const avatarImage = document.querySelector('[data-admin-detail-avatar-image]');
    const avatarInitials = document.querySelector('[data-admin-detail-avatar-initials]');
    const statusBadge = document.querySelector('[data-admin-detail-status-badge]');

    setText('[data-admin-detail-title]', row.getAttribute('data-admin-name'));
    setText('[data-admin-detail-full-name]', row.getAttribute('data-admin-name'));
    setText('[data-admin-detail-email]', row.getAttribute('data-admin-email'));
    setText('[data-admin-detail-email-row]', row.getAttribute('data-admin-email'));
    setText('[data-admin-detail-type]', roleLabel);
    setText('[data-admin-detail-date]', row.getAttribute('data-admin-date-label') || row.getAttribute('data-admin-date'));
    setText('[data-admin-detail-updated]', row.getAttribute('data-admin-last-updated') || 'N/A');
    setText('[data-admin-detail-status]', statusLabel);
    setText('[data-admin-detail-role-badge]', roleLabel);
    setText('[data-admin-detail-status-badge]', statusLabel);
    setText('[data-admin-detail-registered-count]', row.getAttribute('data-admin-registered-count') || '0');
    setText('[data-admin-detail-joined-count]', row.getAttribute('data-admin-joined-count') || row.getAttribute('data-admin-registered-count') || '0');
    setText('[data-admin-detail-liked-count]', row.getAttribute('data-admin-liked-count') || '0');
    setText('[data-admin-detail-created-count]', row.getAttribute('data-admin-created-count') || '0');

    if (avatarImage && avatarInitials) {
        avatarImage.src = avatarSrc;
        avatarImage.hidden = avatarSrc === '';
        avatarInitials.hidden = avatarSrc !== '';
        avatarInitials.textContent = row.getAttribute('data-admin-initials') || 'U';
    }

    if (statusBadge) {
        statusBadge.className = `dashboard-status dashboard-status-${status}`;
    }

    updateUserDetailActions(row);
}

// Updateuserdetailactions
function updateUserDetailActions(row) {
    const userId = row.getAttribute('data-admin-id');
    const status = row.getAttribute('data-admin-status') || '';
    const isCurrentAdmin = row.getAttribute('data-admin-current') === '1';
    const suspendForm = document.querySelector('[data-admin-user-action="suspend"]');
    const reactivateForm = document.querySelector('[data-admin-user-action="reactivate"]');
    const deletePanel = document.querySelector('[data-admin-user-action="delete"]');
    const statusGroup = document.querySelector('[data-admin-user-action-group="status"]');

    setFormValues('[data-admin-user-id]', userId);

    if (suspendForm) {
        suspendForm.hidden = status !== 'active' || isCurrentAdmin;
    }

    if (reactivateForm) {
        reactivateForm.hidden = status !== 'suspended' && status !== 'inactive';
    }

    if (deletePanel) {
        deletePanel.hidden = isCurrentAdmin || status === 'inactive';
    }

    if (statusGroup) {
        const hasVisibleStatusAction = [suspendForm, reactivateForm].some((form) => form && !form.hidden);
        statusGroup.hidden = !hasVisibleStatusAction;
    }
}

// Ensureadminuserformid
function ensureAdminUserFormId(form) {
    const userIdField = form?.querySelector('input[name="user_id"]');

    if (!userIdField) {
        return true;
    }

    if (userIdField.value === '' && activeAdminUserRow) {
        userIdField.value = activeAdminUserRow.getAttribute('data-admin-id') || '';
    }

    return userIdField.value !== '';
}

// Populateuseredit
function populateUserEdit(row) {
    setFormValue('[data-admin-user-edit-field="user_id"]', row.getAttribute('data-admin-id'));
    setFormValue('[data-admin-user-edit-field="first_name"]', row.getAttribute('data-admin-first-name'));
    setFormValue('[data-admin-user-edit-field="last_name"]', row.getAttribute('data-admin-last-name'));
    setFormValue('[data-admin-user-edit-field="email"]', row.getAttribute('data-admin-email'));
    setFormValue('[data-admin-user-edit-field="role"]', row.getAttribute('data-admin-role'));
    setFormValue('[data-admin-user-edit-field="status"]', row.getAttribute('data-admin-status'));
}

// Populateeventdetail
function populateEventDetail(row) {
    activeAdminEventRow = row;

    setText('[data-admin-detail-title]', row.getAttribute('data-admin-name'));
    setText('[data-admin-detail-status-badge]', row.getAttribute('data-admin-status-label'));
    setText('[data-admin-detail-organizer]', row.getAttribute('data-admin-organizer'));
    setText('[data-admin-detail-organizer-line]', row.getAttribute('data-admin-organizer'));
    setText('[data-admin-detail-organizer-email]', row.getAttribute('data-admin-organizer-email'));
    setText('[data-admin-detail-category]', row.getAttribute('data-admin-category'));
    setText('[data-admin-detail-type]', row.getAttribute('data-admin-type-label') || row.getAttribute('data-admin-type'));
    setText('[data-admin-detail-category-badge]', row.getAttribute('data-admin-category') || 'Uncategorized');
    setText('[data-admin-detail-type-badge]', row.getAttribute('data-admin-type-label') || row.getAttribute('data-admin-type'));
    setText('[data-admin-detail-summary]', row.getAttribute('data-admin-summary') || row.getAttribute('data-admin-description') || 'No event summary available.');
    setText('[data-admin-detail-date]', row.getAttribute('data-admin-date-label') || 'N/A');
    setText('[data-admin-detail-time]', row.getAttribute('data-admin-time-label') || 'N/A');
    setText('[data-admin-detail-start-time]', row.getAttribute('data-admin-start-time-label') || 'N/A');
    setText('[data-admin-detail-end-time]', row.getAttribute('data-admin-end-time-label') || 'N/A');
    setText('[data-admin-detail-location]', row.getAttribute('data-admin-location'));
    setText('[data-admin-detail-venue]', row.getAttribute('data-admin-venue') || 'N/A');
    setText('[data-admin-detail-address]', row.getAttribute('data-admin-address') || 'N/A');
    setText('[data-admin-detail-country]', row.getAttribute('data-admin-country'));
    setText('[data-admin-detail-city]', row.getAttribute('data-admin-city') || 'N/A');
    setText('[data-admin-detail-capacity]', row.getAttribute('data-admin-capacity'));
    setText('[data-admin-detail-status]', row.getAttribute('data-admin-status-label'));
    setText('[data-admin-detail-registrations]', row.getAttribute('data-admin-registrations'));
    setText('[data-admin-detail-visibility]', row.getAttribute('data-admin-visibility-label'));
    setText('[data-admin-detail-publish]', row.getAttribute('data-admin-publish-label'));
    setText('[data-admin-detail-created]', row.getAttribute('data-admin-created'));
    setText('[data-admin-detail-description]', row.getAttribute('data-admin-description'));

    const banner = document.querySelector('[data-admin-detail-banner]');
    const statusBadge = document.querySelector('[data-admin-detail-status-badge]');

    if (banner) {
        banner.src = row.getAttribute('data-admin-banner') || '../assets/images/events/hero-event.png';
    }

    if (statusBadge) {
        statusBadge.className = `dashboard-status dashboard-status-${row.getAttribute('data-admin-status') || 'pending'}`;
    }

    updateEventDetailActions(row);
}

// Updateeventdetailactions
function updateEventDetailActions(row) {
    const eventId = row.getAttribute('data-admin-id');
    const status = row.getAttribute('data-admin-status') || '';
    const setActionVisibility = (actionName, shouldShow) => {
        document.querySelectorAll(`[data-admin-detail-action="${actionName}"]`).forEach((action) => {
            action.hidden = !shouldShow;
        });
    };

    setFormValues('[data-admin-detail-event-id]', eventId);
    setActionVisibility('approve-publish', !['published', 'rejected', 'closed', 'cancelled'].includes(status));
    setActionVisibility('reject-close', !['rejected', 'closed', 'cancelled'].includes(status));
    setActionVisibility('delete', true);

    document.querySelectorAll('[data-admin-detail-action-group="status"]').forEach((group) => {
        const hasVisibleAction = Array.from(group.querySelectorAll('[data-admin-detail-action]'))
            .some((action) => !action.hidden);

        group.hidden = !hasVisibleAction;
    });
}

// Populateeventedit
function populateEventEdit(row) {
    setFormValue('[data-admin-edit-field="event_id"]', row.getAttribute('data-admin-id'));
    setFormValue('[data-admin-edit-field="event_title"]', row.getAttribute('data-admin-name'));
    setFormValue('[data-admin-edit-field="event_summary"]', row.getAttribute('data-admin-summary'));
    setFormValue('[data-admin-edit-field="event_description"]', row.getAttribute('data-admin-description'));
    setFormValue('[data-admin-edit-field="event_tags"]', row.getAttribute('data-admin-tags'));
    setFormValue('[data-admin-edit-field="event_category"]', row.getAttribute('data-admin-category'));
    setFormValue('[data-admin-edit-field="event_type"]', row.getAttribute('data-admin-type'));
    setFormValue('[data-admin-edit-field="status"]', row.getAttribute('data-admin-status'));
    setFormValue('[data-admin-edit-field="event_date"]', row.getAttribute('data-admin-date'));
    setFormValue('[data-admin-edit-field="event_time"]', row.getAttribute('data-admin-time'));
    setFormValue('[data-admin-edit-field="event_end_time"]', row.getAttribute('data-admin-end-time'));
    setFormValue('[data-admin-edit-field="capacity"]', row.getAttribute('data-admin-capacity'));
    setFormValue('[data-admin-edit-field="event_location"]', row.getAttribute('data-admin-location'));
    setFormValue('[data-admin-edit-field="event_country"]', row.getAttribute('data-admin-country'));
    setFormValue('[data-admin-edit-field="event_city"]', row.getAttribute('data-admin-city'));
    setFormValue('[data-admin-edit-field="event_venue"]', row.getAttribute('data-admin-venue'));
    setFormValue('[data-admin-edit-field="event_address"]', row.getAttribute('data-admin-address'));
    setFormValue('[data-admin-edit-field="online_platform"]', row.getAttribute('data-admin-online-platform'));
    setFormValue('[data-admin-edit-field="online_link"]', row.getAttribute('data-admin-online-link'));
    setFormValue('[data-admin-edit-field="visibility"]', row.getAttribute('data-admin-visibility'));
    setFormValue('[data-admin-edit-field="audience"]', row.getAttribute('data-admin-audience'));
    setFormValue('[data-admin-edit-field="publish_date"]', row.getAttribute('data-admin-publish-date'));
    setFormValue('[data-admin-edit-field="publish_time"]', row.getAttribute('data-admin-publish-time'));
}

// Showadminmessage
function showAdminMessage(title, message) {
    setText('[data-admin-message-title]', title);
    setText('[data-admin-message-copy]', message);
    openModal(adminMessageModal);
}

// Appendapprovalhistory
function appendApprovalHistory(action, detail) {
    if (!approvalHistory) {
        return;
    }

    const item = document.createElement('div');
    const typeLabel = document.createElement('span');
    const detailText = document.createElement('strong');
    const timeText = document.createElement('small');

    item.className = 'admin-activity-item';
    typeLabel.textContent = action;
    detailText.textContent = detail;
    timeText.textContent = 'Just now';
    item.append(typeLabel, detailText, timeText);
    approvalHistory.prepend(item);
}

// Updatereviewpanel
function updateReviewPanel(button) {
    if (!button) {
        return;
    }

    reviewSelectButtons.forEach((reviewButton) => reviewButton.classList.remove('active'));
    button.classList.add('active');

    const title = button.getAttribute('data-title');
    const organizer = button.getAttribute('data-organizer');
    const banner = button.getAttribute('data-banner');

    setText('[data-review-title]', title);
    setText('[data-review-organizer]', `Organizer: ${organizer}`);
    setText('[data-review-category]', button.getAttribute('data-category'));
    setText('[data-review-date]', button.getAttribute('data-date'));
    setText('[data-review-time]', button.getAttribute('data-time'));
    setText('[data-review-location]', button.getAttribute('data-location'));
    setText('[data-review-capacity]', `${button.getAttribute('data-capacity')} attendees`);
    setText('[data-review-registrations]', `${button.getAttribute('data-registrations') || 0} attendees`);
    setText('[data-review-description]', button.getAttribute('data-description'));
    setText('[data-review-details]', button.getAttribute('data-details'));
    setText('[data-review-visibility]', button.getAttribute('data-visibility'));
    setText('[data-review-publish]', button.getAttribute('data-publish'));
    setText('[data-review-status]', 'Pending');
    setFormValue('[data-review-event-id]', button.getAttribute('data-event-id'));

    if (banner) {
        const reviewBanner = document.querySelector('[data-review-banner]');

        if (reviewBanner) {
            reviewBanner.src = banner;
        }
    }

    appendApprovalHistory('Opened Review', `${title} selected for review.`);
}

// Setbannerpreview
function setBannerPreview(src) {
    eventWizardState.bannerSrc = src;

    if (bannerPreview) {
        bannerPreview.src = src;
        bannerPreview.hidden = false;
    }

    if (bannerDropzone) {
        bannerDropzone.classList.add('has-preview');
    }

    if (summaryBannerImage) {
        summaryBannerImage.src = src;
        summaryBannerImage.hidden = false;
    }

    if (summaryBannerPlaceholder) {
        summaryBannerPlaceholder.hidden = true;
    }
}

// Handlebannerfile
function handleBannerFile(file) {
    if (!file) {
        return;
    }

    if (!allowedBannerTypes.includes(file.type) || !allowedBannerFilePattern.test(file.name || '')) {
        bannerDropzone?.classList.add('is-invalid');
        setWizardError(2, 'Event banner must be JPG, PNG, or WEBP.');
        return;
    }

    const reader = new FileReader();

    reader.addEventListener('load', () => {
        setWizardError(2, '');
        bannerInput?.classList.remove('is-invalid');
        bannerDropzone?.classList.remove('is-invalid');
        setBannerPreview(reader.result);
    });

    reader.readAsDataURL(file);
}

// Focusfirstmodalcontrol
function focusFirstModalControl(modal) {
    const focusableElement = modal?.querySelector('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled])');

    focusableElement?.focus();
}

// Openmodal
function openModal(modal) {
    if (!modal) {
        return;
    }

    activeModal = modal;
    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');

    requestAnimationFrame(() => {
        modal.classList.add('is-open');
        focusFirstModalControl(modal);
    });
}

// Openauthrequiredmodal
function openAuthRequiredModal(message, title = 'Please sign in first') {
    if (authRequiredTitle) {
        authRequiredTitle.textContent = title;
    }

    if (authRequiredMessage) {
        authRequiredMessage.textContent = message;
    }

    openModal(authRequiredModal);
}

// Getlandingscrolloffset
function getLandingScrollOffset() {
    const headerHeight = document.querySelector('.site-header')?.getBoundingClientRect().height || 72;

    return headerHeight + 18;
}

// Issectioncurrentlyactive
function isSectionCurrentlyActive(section, offset) {
    const rect = section.getBoundingClientRect();

    return rect.top <= offset + 8 && rect.bottom > offset + 8;
}

// Scrolltolandingeventssection
function scrollToLandingEventsSection() {
    if (!landingEventsSection) {
        return;
    }

    const offset = getLandingScrollOffset();

    if (isSectionCurrentlyActive(landingEventsSection, offset)) {
        return;
    }

    const targetTop = Math.max(0, window.scrollY + landingEventsSection.getBoundingClientRect().top - offset);
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (Math.abs(window.scrollY - targetTop) < 4) {
        return;
    }

    window.scrollTo({
        top: targetTop,
        behavior: prefersReducedMotion ? 'auto' : 'smooth',
    });
}

// Resetregistrationmodal
function resetRegistrationModal() {
    if (registrationForm) {
        registrationForm.reset();
        registrationForm.hidden = false;
    }

    if (registrationSuccess) {
        registrationSuccess.hidden = true;
    }

    if (registrationSubmit) {
        registrationSubmit.disabled = true;
    }

    if (registrationTitle) {
        registrationTitle.textContent = 'Event Registration';
    }

    if (registrationDate) {
        registrationDate.textContent = 'Event date';
    }

    if (registrationTime) {
        registrationTime.textContent = 'Event time';
    }

    if (registrationLocation) {
        registrationLocation.textContent = 'Event location';
    }

    if (registrationEventId) {
        registrationEventId.value = '';
    }
}

// Closemodal
function closeModal(modal) {
    if (!modal) {
        return;
    }

    const shouldResetRegistration = modal === registrationModal;

    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');

    setTimeout(() => {
        if (!modal.classList.contains('is-open')) {
            modal.hidden = true;

            if (shouldResetRegistration) {
                resetRegistrationModal();
            }
        }
    }, 220);

    if (activeModal === modal) {
        activeModal = null;
    }

    if (!document.querySelector('.registration-modal-overlay.is-open')) {
        document.body.classList.remove('modal-open');
    }
}

// Closeactivemodal
function closeActiveModal() {
    if (activeModal) {
        closeModal(activeModal);
    }
}

// Spliteventdatetime
function splitEventDateTime(dateTimeText) {
    const parts = dateTimeText.split(' - ');
    const date = parts[0]?.trim();
    const time = parts.slice(1).join(' - ').trim();

    return {
        date: date || 'Event date',
        time: time || 'Event time',
    };
}

// Getregistrationeventdetails
function getRegistrationEventDetails(button) {
    const card = button.closest('[data-registration-event], .event-card');
    const dateTimeText = card?.getAttribute('data-event-date-time')
        || card?.querySelector('.event-time')?.textContent.trim()
        || '';
    const dateTime = splitEventDateTime(dateTimeText);
    const locationText = card?.getAttribute('data-event-location')
        || card?.querySelector('.event-location')?.textContent.trim()
        || 'Event location';
    const onlineLocationPattern = /online|virtual|live|meet|session|studio|room/i;

    return {
        eventId: button.getAttribute('data-registration-event-id') || card?.getAttribute('data-event-id')?.replace('event-', '') || '',
        price: button.getAttribute('data-registration-price') || card?.querySelector('.event-registration-label')?.textContent.trim() || 'Free Registration',
        title: card?.getAttribute('data-event-title')
            || card?.querySelector('h3')?.textContent.trim()
            || 'Event Registration',
        date: dateTime.date,
        time: dateTime.time,
        location: onlineLocationPattern.test(locationText)
            ? `${locationText} - online link will be provided`
            : locationText,
    };
}

// Populateregistrationmodal
function populateRegistrationModal(button) {
    const details = getRegistrationEventDetails(button);

    resetRegistrationModal();

    if (registrationTitle) {
        registrationTitle.textContent = details.title;
    }

    if (registrationDate) {
        registrationDate.textContent = details.date;
    }

    if (registrationTime) {
        registrationTime.textContent = details.time;
    }

    if (registrationLocation) {
        registrationLocation.textContent = details.location;
    }

    if (registrationEventId) {
        registrationEventId.value = details.eventId;
    }
}

pillMenus.forEach((menu) => {
    const items = menu.querySelectorAll(':scope > a, :scope > button');
    const activeItem = menu.querySelector('.active') || (menu.classList.contains('no-active') ? null : items[0]);

    moveIndicator(menu, activeItem);

    items.forEach((item) => {
        item.addEventListener('mouseenter', () => {
            items.forEach((menuItem) => menuItem.classList.remove('indicator-target'));
            item.classList.add('indicator-target');
            moveIndicator(menu, item);
        });

        item.addEventListener('focus', () => {
            items.forEach((menuItem) => menuItem.classList.remove('indicator-target'));
            item.classList.add('indicator-target');
            moveIndicator(menu, item);
        });

        if (item.tagName === 'BUTTON' && !item.closest('.pagination')) {
            item.addEventListener('click', () => {
                items.forEach((menuItem) => menuItem.classList.remove('active'));
                item.classList.add('active');
                moveIndicator(menu, item);
            });
        }
    });

    menu.addEventListener('mouseleave', () => {
        items.forEach((menuItem) => menuItem.classList.remove('indicator-target'));
        moveIndicator(menu, menu.querySelector('.active') || activeItem);
    });

    menu.addEventListener('focusout', () => {
        setTimeout(() => {
            if (!menu.contains(document.activeElement)) {
                items.forEach((menuItem) => menuItem.classList.remove('indicator-target'));
                moveIndicator(menu, menu.querySelector('.active') || activeItem);
            }
        }, 0);
    });
});

window.addEventListener('resize', () => {
    pillMenus.forEach((menu) => {
        moveIndicator(menu, menu.querySelector('.indicator-target') || menu.querySelector('.active'));
    });

    updateCarouselButtons();
});

if (menuButton && navigation) {
    menuButton.addEventListener('click', () => {
        const isOpen = navigation.classList.toggle('open');
        menuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

        if (isOpen) {
            moveIndicator(navigation, navigation.querySelector('.active'));
        }
    });
}

if (passwordToggle) {
    passwordToggle.addEventListener('click', togglePassword);

    passwordToggle.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            togglePassword();
        }
    });
}

if (profileToggle && profileMenu) {
    profileToggle.addEventListener('click', () => {
        const isOpen = profileMenu.classList.toggle('open');
        profileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
}

if (landingBrowseEventsButton && landingEventsSection) {
    landingBrowseEventsButton.addEventListener('click', (event) => {
        event.preventDefault();
        scrollToLandingEventsSection();
    });
}

clickableEventCards.forEach((card) => {
    const openCard = () => {
        const targetHref = card.getAttribute('data-event-href');

        if (targetHref) {
            window.location.href = targetHref;
        }
    };

    card.addEventListener('click', (event) => {
        if (event.target.closest('a, button, input, select, textarea, label, form')) {
            return;
        }

        openCard();
    });

    card.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        if (event.target.closest('a, button, input, select, textarea, label, form')) {
            return;
        }

        event.preventDefault();
        openCard();
    });
});

authRequiredRegisterButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
        event.stopPropagation();
        openAuthRequiredModal('Please sign in to register for this event.');
    });
});

authRequiredLikeButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
        event.stopPropagation();
        openAuthRequiredModal('Please sign in to like this event.');
    });
});

authRequiredCityLinks.forEach((link) => {
    link.addEventListener('click', (event) => {
        event.preventDefault();
        openAuthRequiredModal('Please sign in to browse events by city.');
    });
});

eventRegisterButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
        event.stopPropagation();
        populateRegistrationModal(button);
        openModal(registrationModal);
    });
});

privateEventOpenButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
        event.preventDefault();
        openModal(privateEventModal);
    });
});

// Attendance Key Toggle Listener
document.querySelectorAll('[data-attendance-toggle]').forEach((button) => {
    button.addEventListener('click', (event) => {
        event.stopPropagation();
        const value = button.querySelector('[data-attendance-value]');
        const isVisible = button.getAttribute('aria-pressed') === 'true';

        button.setAttribute('aria-pressed', isVisible ? 'false' : 'true');

        if (value) {
            value.textContent = isVisible ? '••••••••••••' : (button.dataset.attendanceCode || 'No code');
        }
    });
});

modalCloseButtons.forEach((button) => {
    button.addEventListener('click', () => {
        closeModal(button.closest('.registration-modal-overlay'));
    });
});

[authRequiredModal, registrationModal, privateEventModal].forEach((modal) => {
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal(modal);
        }
    });
});

if (new URLSearchParams(window.location.search).get('private_event') === '1') {
    openModal(privateEventModal);
}

if (registrationConfirm && registrationSubmit) {
    registrationConfirm.addEventListener('change', () => {
        registrationSubmit.disabled = !registrationConfirm.checked;
    });
}

if (registrationForm) {
    registrationForm.addEventListener('submit', (event) => {
        if (!registrationConfirm?.checked) {
            event.preventDefault();
            return;
        }

        if (registrationEventId?.value) {
            return;
        }

        event.preventDefault();

        registrationForm.reset();
        registrationForm.hidden = true;

        if (registrationSubmit) {
            registrationSubmit.disabled = true;
        }

        if (registrationSuccess) {
            registrationSuccess.hidden = false;
            focusFirstModalControl(registrationSuccess);
        }
    });
}

if (eventWizard) {
    eventWizard.addEventListener('input', (event) => {
        if (event.target.matches('input, select, textarea')) {
            if (event.target.matches('[data-event-date]')) {
                event.target.value = normalizeEventDateInput(event.target.value);
            }

            event.target.classList.remove('is-invalid');
            updateMapPreview();
            updatePublishSummary();
        }
    });

    eventWizard.addEventListener('change', (event) => {
        if (event.target.matches('input, select, textarea')) {
            if (event.target.matches('[data-event-country]')) {
                updateCityOptionsForCountry(false);
            }

            event.target.classList.remove('is-invalid');
            updateMapPreview();
            updatePublishSummary();
        }
    });

    eventWizard.addEventListener('submit', (event) => {
        if (eventWizardState.existingEvent) {
            if (!validateWizardStep(1)) {
                event.preventDefault();
                updateWizardStep(1);
            }

            return;
        }

        let firstInvalidStep = 0;

        [1, 2, 3].forEach((step) => {
            if (firstInvalidStep === 0 && !validateWizardStep(step)) {
                firstInvalidStep = step;
            }
        });

        if (firstInvalidStep > 0) {
            event.preventDefault();
            updateWizardStep(firstInvalidStep);
            return;
        }

        const messageBox = document.querySelector('[data-publish-message]');

        eventWizardState.completedSteps.add(1);
        eventWizardState.completedSteps.add(2);
        eventWizardState.completedSteps.add(3);

        if (messageBox) {
            messageBox.textContent = 'Saving event...';
        }
    });

    wizardStepItems.forEach((item) => {
        item.addEventListener('click', () => {
            const requestedStep = Number(item.getAttribute('data-step-nav'));

            if (eventWizardState.existingEvent) {
                eventWizardState.completedSteps.add(1);
                eventWizardState.completedSteps.add(2);
                eventWizardState.completedSteps.add(3);
                updateWizardStep(requestedStep);
                return;
            }

            if (requestedStep <= eventWizardState.currentStep) {
                updateWizardStep(requestedStep);
                return;
            }

            for (let step = 1; step < requestedStep; step += 1) {
                if (!validateWizardStep(step)) {
                    updateWizardStep(step);
                    return;
                }

                eventWizardState.completedSteps.add(step);
            }

            updateWizardStep(requestedStep);
        });
    });

    wizardNextButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const nextStep = Number(button.getAttribute('data-next-step'));

            if (!validateWizardStep(eventWizardState.currentStep)) {
                return;
            }

            eventWizardState.completedSteps.add(eventWizardState.currentStep);
            updateWizardStep(nextStep);
        });
    });

    if (createEventButton) {
        createEventButton.addEventListener('click', (event) => {
            const messageBox = document.querySelector('[data-publish-message]');
            let firstInvalidStep = 0;

            [1, 2, 3].forEach((step) => {
                if (firstInvalidStep === 0 && !validateWizardStep(step)) {
                    firstInvalidStep = step;
                }
            });

            if (firstInvalidStep > 0) {
                event.preventDefault();
                updateWizardStep(firstInvalidStep);
                return;
            }

            eventWizardState.completedSteps.add(1);
            eventWizardState.completedSteps.add(2);
            eventWizardState.completedSteps.add(3);
            updateWizardStep(3);

            if (messageBox) {
                messageBox.textContent = 'Saving event...';
            }
        });
    }

    visibilityOptions.forEach((option) => {
        option.addEventListener('change', updatePublishControls);
    });

    scheduleOptions.forEach((option) => {
        option.addEventListener('change', updatePublishControls);
    });

    locationTypeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const selectedType = button.getAttribute('data-location-type-option') || 'Venue';
            const locationField = getWizardField('[data-event-location]');

            locationTypeButtons.forEach((typeButton) => typeButton.classList.remove('active'));
            button.classList.add('active');

            if (locationField) {
                locationField.value = selectedType;
            }

            updateLocationTypeFields();
        });
    });

    if (onlinePlatformSelect) {
        onlinePlatformSelect.addEventListener('change', updateLocationTypeFields);
    }

    if (eventWizardState.existingEvent) {
        eventWizardState.completedSteps.add(1);
        eventWizardState.completedSteps.add(2);
        eventWizardState.completedSteps.add(3);
    }

    updateCityOptionsForCountry(true);
    updatePublishControls();
    updateLocationTypeFields();
    updateWizardStep(eventWizardState.currentStep);
}

if (bannerInput) {
    bannerInput.addEventListener('change', () => {
        handleBannerFile(bannerInput.files?.[0]);
    });
}

if (bannerDropzone) {
    ['dragenter', 'dragover'].forEach((eventName) => {
        bannerDropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            bannerDropzone.classList.add('is-dragging');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        bannerDropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            bannerDropzone.classList.remove('is-dragging');
        });
    });

    bannerDropzone.addEventListener('drop', (event) => {
        handleBannerFile(event.dataTransfer?.files?.[0]);
    });
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeActiveModal();
        closeLocationSelect();
        profileMenu?.classList.remove('open');
        profileToggle?.setAttribute('aria-expanded', 'false');
    }
});

locationModeButtons.forEach((button) => {
    button.addEventListener('click', () => {
        locationModeButtons.forEach((modeButton) => modeButton.classList.remove('active'));
        button.classList.add('active');
        updateLocation(button.getAttribute('data-location-mode'));
        activeCountryFilter = '';

        if (button.hasAttribute('data-location-filter')) {
            activeLocationFilter = button.getAttribute('data-location-filter') || 'all';
            filterEvents();
        }

        if (button.hasAttribute('data-ticket-status-filter')) {
            activeTicketStatusFilter = button.getAttribute('data-ticket-status-filter') || 'all';
            filterEvents();
        }

        if (button.hasAttribute('data-open-country-list')) {
            locationSelect?.classList.add('country-open');
            return;
        }

        locationSelect?.classList.remove('country-open');
        closeLocationSelect();
    });
});

if (locationSelectToggle && locationSelect) {
    locationSelectToggle.addEventListener('click', () => {
        const isOpen = locationSelect.classList.toggle('open');
        locationSelectToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
}

countryOptions.forEach((button) => {
    button.addEventListener('click', (event) => {
        const country = button.getAttribute('data-country-option');

        if (button.tagName === 'BUTTON') {
            event.preventDefault();
        }

        countryOptions.forEach((countryButton) => countryButton.classList.remove('active'));
        button.classList.add('active');
        activeLocationFilter = 'all';
        activeCountryFilter = country || '';
        updateLocation(country);
        filterEvents();
        closeLocationSelect();
    });
});

if (carouselPreviousButton) {
    carouselPreviousButton.addEventListener('click', () => {
        scrollDestinations(-1);
    });
}

if (carouselNextButton) {
    carouselNextButton.addEventListener('click', () => {
        scrollDestinations(1);
    });
}

if (destinationTrack) {
    destinationTrack.addEventListener('scroll', updateCarouselButtons);
    updateCarouselButtons();
}

locationChoiceButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const choice = button.getAttribute('data-location-choice');

        locationChoiceButtons.forEach((choiceButton) => choiceButton.classList.remove('active'));
        button.classList.add('active');
        updateLocation(choice);

        if (choice === 'Near You' && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                () => updateLocation('Near You'),
                () => updateLocation('Near You')
            );
        }
    });
});

// Event Filter Listeners
filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
        activeEventFilter = button.getAttribute('data-filter');
        filterEvents();
    });
});

categoryButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const selectedCategory = button.getAttribute('data-category-filter') || 'all';
        const shouldClearCategory = activeCategoryFilter === selectedCategory;

        activeCategoryFilter = shouldClearCategory ? 'all' : selectedCategory;
        categoryButtons.forEach((categoryButton) => categoryButton.classList.remove('active'));

        if (!shouldClearCategory) {
            button.classList.add('active');
        }

        filterEvents();
    });
});

dashboardFilterButtons.forEach((button) => {
    button.addEventListener('click', () => {
        activeDashboardFilter = button.getAttribute('data-dashboard-filter') || 'all';
        filterDashboardRows();
    });
});

// Admin Filter Listeners
adminFilterButtons.forEach((button) => {
    button.addEventListener('click', () => {
        activeAdminFilter = button.getAttribute('data-admin-filter') || 'all';
        currentAdminPage = 1;
        filterAdminRows();
    });
});

if (adminSearchInput) {
    adminSearchInput.addEventListener('input', () => {
        currentAdminPage = 1;
        filterAdminRows();
    });
}

if (adminSortSelect) {
    adminSortSelect.addEventListener('change', () => {
        sortAdminRows();
        currentAdminPage = 1;
        filterAdminRows();
    });
}

document.querySelectorAll('[data-admin-pagination]').forEach((paginationMenu) => {
    paginationMenu.addEventListener('click', (event) => {
        const button = event.target.closest('button');

        if (!button || !paginationMenu.contains(button)) {
            return;
        }

        const selectedPage = Number(button.getAttribute('data-admin-page'));

        if (selectedPage) {
            currentAdminPage = selectedPage;
        } else if (button.hasAttribute('data-admin-page-previous')) {
            currentAdminPage = Math.max(1, currentAdminPage - 1);
        } else if (button.hasAttribute('data-admin-page-next')) {
            currentAdminPage += 1;
        }

        filterAdminRows();
        moveIndicator(paginationMenu, paginationMenu.querySelector('.active'));
    });
});

document.querySelectorAll('[data-user-detail]').forEach((button) => {
    button.addEventListener('click', () => {
        const row = button.closest('[data-admin-row]');

        if (row) {
            populateUserDetail(row);
            openModal(adminDetailModal);
        }
    });
});

document.querySelectorAll('[data-user-detail-edit]').forEach((button) => {
    button.addEventListener('click', () => {
        if (!activeAdminUserRow) {
            return;
        }

        populateUserEdit(activeAdminUserRow);
        closeModal(adminDetailModal);
        openModal(adminUserEditModal);
    });
});

document.querySelectorAll('[data-admin-user-action-form], [data-admin-user-edit-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!ensureAdminUserFormId(form)) {
            event.preventDefault();
            window.alert('Please open a user record before submitting an action.');
        }
    });
});

document.querySelectorAll('[data-event-detail]').forEach((button) => {
    button.addEventListener('click', () => {
        const row = button.closest('[data-admin-row]');

        if (row) {
            populateEventDetail(row);
            openModal(adminDetailModal);
        }
    });
});

document.querySelectorAll('[data-event-edit]').forEach((button) => {
    button.addEventListener('click', () => {
        const row = button.closest('[data-admin-row]');

        if (row) {
            populateEventEdit(row);
            openModal(adminEditModal);
        }
    });
});

document.querySelectorAll('[data-event-detail-edit]').forEach((button) => {
    button.addEventListener('click', () => {
        if (!activeAdminEventRow) {
            return;
        }

        populateEventEdit(activeAdminEventRow);
        closeModal(adminDetailModal);
        openModal(adminEditModal);
    });
});

document.querySelectorAll('[data-admin-action]').forEach((button) => {
    button.addEventListener('click', () => {
        const action = button.getAttribute('data-admin-action');
        const row = button.closest('[data-admin-row]');
        const itemName = row?.getAttribute('data-admin-name') || 'Selected record';

        showAdminMessage(action, `${itemName} is ready for ${action.toLowerCase()} backend processing.`);
    });
});

document.querySelectorAll('[data-approval-action]').forEach((button) => {
    button.addEventListener('click', () => {
        const row = button.closest('[data-admin-row]');
        const status = button.getAttribute('data-approval-action') || 'approved';
        const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
        const statusBadge = row?.querySelector('.dashboard-status');

        if (row && statusBadge) {
            row.setAttribute('data-admin-status', status);
            row.setAttribute('data-admin-status-label', statusLabel);
            statusBadge.className = `dashboard-status dashboard-status-${status}`;
            statusBadge.textContent = statusLabel;
            filterAdminRows();
        }

        showAdminMessage(`${statusLabel} event`, `${row?.getAttribute('data-admin-name') || 'Selected event'} was marked as ${statusLabel.toLowerCase()} in the frontend preview.`);
    });
});

reviewSelectButtons.forEach((button) => {
    button.addEventListener('click', () => updateReviewPanel(button));
});

document.querySelectorAll('[data-review-action]').forEach((button) => {
    button.addEventListener('click', () => {
        const status = button.getAttribute('data-review-action') || 'approved';
        const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
        const reviewStatus = document.querySelector('[data-review-status]');
        const title = document.querySelector('[data-review-title]')?.textContent || 'Selected event';

        if (reviewStatus) {
            reviewStatus.className = `dashboard-status dashboard-status-${status}`;
            reviewStatus.textContent = statusLabel;
        }

        appendApprovalHistory(statusLabel, `${title} was marked as ${statusLabel.toLowerCase()} by Admin Name.`);
    });
});

document.querySelectorAll('[data-request-changes]').forEach((button) => {
    button.addEventListener('click', () => {
        openModal(revisionModal);
    });
});

if (revisionForm) {
    revisionForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const feedback = revisionForm.querySelector('[data-revision-feedback]')?.value.trim();
        const title = document.querySelector('[data-review-title]')?.textContent || 'Selected event';
        const reviewStatus = document.querySelector('[data-review-status]');

        if (!feedback) {
            return;
        }

        if (reviewStatus) {
            reviewStatus.className = 'dashboard-status dashboard-status-pending';
            reviewStatus.textContent = 'Changes Requested';
        }

        appendApprovalHistory('Revision Requested', `${title}: ${feedback}`);
        revisionForm.reset();
        closeModal(revisionModal);
    });
}

filterDashboardRows();
sortAdminRows();
filterAdminRows();
filterEvents();

document.addEventListener('click', (event) => {
    if (locationSelect && !locationSelect.contains(event.target)) {
        closeLocationSelect();
    }

    if (profileMenu && !profileMenu.contains(event.target)) {
        profileMenu.classList.remove('open');
        profileToggle?.setAttribute('aria-expanded', 'false');
    }
});
