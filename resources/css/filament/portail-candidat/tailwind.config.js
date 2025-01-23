import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/PortailCandidat/**/*.php',
        './resources/views/filament/portail-candidat/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
