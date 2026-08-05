function placeholderMapsSearch(name) {
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(name)}`;
}

const PLACEHOLDER_LOCATION_NAMES = [
    '関東支社',
    '天神営業所',
    '梅田営業所',
];

// TODO: replace with the real Google Maps review link once available.
const PLACEHOLDER_LINKS = Object.fromEntries(
    PLACEHOLDER_LOCATION_NAMES.map((name) => [name, placeholderMapsSearch(name)]),
);

export const LOCATION_REVIEW_LINKS = {
    ...PLACEHOLDER_LINKS,
    '中部支社': 'https://g.page/r/CVb03IvsgMiZEAE/review',
    '神戸営業所': 'https://g.page/r/CSsey1cOplxHEAE/review',
    '熊本営業所': 'https://g.page/r/CdQMH8YEDBGSEAE/review',
    '京都営業所': 'https://g.page/r/CVvXwcO58tkzEAE/review',
};
