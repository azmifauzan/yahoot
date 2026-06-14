const WORDS = {
    id: {
        adjectives: [
            'Cepat', 'Lincah', 'Ceria', 'Pintar', 'Kuat', 'Gigih', 'Jago',
            'Hebat', 'Keren', 'Tangkas', 'Berani', 'Licin', 'Tajam', 'Jenius', 'Kilat',
        ],
        nouns: [
            'Panda', 'Singa', 'Elang', 'Naga', 'Rubah', 'Harimau', 'Beruang',
            'Kucing', 'Anjing', 'Burung', 'Kuda', 'Lebah', 'Kupu', 'Ikan', 'Ayam',
        ],
    },
    en: {
        adjectives: [
            'Happy', 'Fast', 'Brave', 'Clever', 'Mighty', 'Lucky', 'Silly',
            'Jolly', 'Swift', 'Bold', 'Wild', 'Cosmic', 'Funky', 'Crazy', 'Sneaky',
        ],
        nouns: [
            'Panda', 'Tiger', 'Eagle', 'Dragon', 'Fox', 'Wolf', 'Bear',
            'Cat', 'Dog', 'Falcon', 'Otter', 'Shark', 'Hawk', 'Lion', 'Koala',
        ],
    },
};

/**
 * Generate a random, fun nickname (e.g. "CepatPanda42").
 *
 * Always fits within the nickname length constraints (2-20 characters).
 */
export function randomNickname(locale = 'id') {
    const words = WORDS[locale] ?? WORDS.id;
    const adjective = words.adjectives[Math.floor(Math.random() * words.adjectives.length)];
    const noun = words.nouns[Math.floor(Math.random() * words.nouns.length)];
    const number = Math.floor(Math.random() * 100);

    return `${adjective}${noun}${number}`;
}
