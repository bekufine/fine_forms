export const LOCATION_REVIEW_LINKS = {
    '関東支社': 'https://maps.app.goo.gl/QV9DYWDc1aEQgFMR6',
    '中部支社': 'https://g.page/r/CVb03IvsgMiZEAE/review',
    '天神営業所': 'https://maps.app.goo.gl/NzKsV2BVurPP21k99',
    '梅田営業所': 'https://maps.app.goo.gl/8CUr2verADqGLSxS9',
    '神戸営業所': 'https://g.page/r/CSsey1cOplxHEAE/review',
    '熊本営業所': 'https://g.page/r/CdQMH8YEDBGSEAE/review',
    '京都営業所': 'https://g.page/r/CVvXwcO58tkzEAE/review',
};

// Form-specific override: always show this review link after submission,
// regardless of which office the respondent selected.
// Keyed by form title (not id) — seeders use firstOrCreate, so the same form
// can end up with a different auto-increment id in each environment.
export const FORM_REVIEW_LINKS = {
    'ご来社アンケート': 'https://g.page/r/CfYPZMDXRtL1EAE/review',
};
