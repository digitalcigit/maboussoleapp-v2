import { marked } from 'marked';

// Configuration de marked
marked.setOptions({
    breaks: true, // Convertit les retours à la ligne en <br>
    gfm: true,    // Active GitHub Flavored Markdown
});

// Rend marked disponible globalement pour le composant de prévisualisation
window.marked = marked;
