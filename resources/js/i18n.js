import { createI18n } from 'vue-i18n';
import id from './locales/id.json';
import en from './locales/en.json';

const i18n = createI18n({
    legacy: false,
    locale: 'id',
    fallbackLocale: 'id',
    messages: {
        id,
        en,
    },
});

export default i18n;
