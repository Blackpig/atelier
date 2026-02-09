<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Atelier French Language Lines
    |--------------------------------------------------------------------------
    */

    'blocks' => [
        'hero' => [
            'label' => 'Section Hero',
            'description' => 'Section hero pleine largeur avec image de fond, titre et appel à l\'action.',
        ],
        'text_with_two_images' => [
            'label' => 'Texte avec Deux Images',
            'description' => 'Contenu texte riche avec deux images d\'accompagnement dans diverses configurations de mise en page.',
        ],
    ],

    'fields' => [
        'headline' => 'Titre principal',
        'subheadline' => 'Sous-titre',
        'description' => 'Description',
        'content' => 'Contenu',
        'title' => 'Titre',
        'cta_text' => 'Texte du bouton',
        'cta_url' => 'URL du bouton',
        'cta_new_tab' => 'Ouvrir dans un nouvel onglet',
        'background_image' => 'Image de fond',
        'image' => 'Image',
        'image_caption' => 'Légende de l\'image',
        'overlay_opacity' => 'Opacité du calque',
        'text_color' => 'Couleur du texte',
        'height' => 'Hauteur de section',
        'content_alignment' => 'Alignement du contenu',
        'layout' => 'Style de mise en page',
        'image_aspect' => 'Ratio d\'aspect de l\'image',
        'image_size' => 'Taille de l\'image',
    ],

    'options' => [
        'overlay' => [
            'none' => 'Aucun',
            'light' => 'Léger (20%)',
            'medium' => 'Moyen (40%)',
            'dark' => 'Sombre (60%)',
            'very_dark' => 'Très sombre (80%)',
        ],
        'text_color' => [
            'white' => 'Blanc',
            'dark' => 'Gris foncé',
            'primary' => 'Couleur primaire',
        ],
        'height' => [
            'small' => 'Petit (400px)',
            'medium' => 'Moyen (600px)',
            'large' => 'Grand (800px)',
            'full_screen' => 'Plein écran',
        ],
        'alignment' => [
            'left' => 'Gauche',
            'center' => 'Centre',
            'right' => 'Droite',
        ],
        'layout' => [
            'images_left' => 'Images à gauche, texte à droite',
            'images_right' => 'Images à droite, texte à gauche',
            'images_stacked_left' => 'Images empilées à gauche, texte à droite',
            'images_stacked_right' => 'Images empilées à droite, texte à gauche',
            'images_top' => 'Images côte à côte au-dessus du texte',
            'images_bottom' => 'Images côte à côte sous le texte',
            'masonry' => 'Grille en maçonnerie (texte + images mélangés)',
        ],
        'aspect' => [
            'square' => 'Carré (1:1)',
            'video' => 'Vidéo (16:9)',
            'standard' => 'Standard (4:3)',
            'portrait' => 'Portrait (3:4)',
            'auto' => 'Auto (naturel)',
        ],
        'size' => [
            'small' => 'Petit (30% de largeur)',
            'medium' => 'Moyen (40% de largeur)',
            'large' => 'Grand (50% de largeur)',
        ],
    ],

    'sections' => [
        'content' => 'Contenu',
        'settings' => 'Paramètres',
        'display_options' => 'Options d\'affichage',
        'images' => 'Images',
        'layout' => 'Mise en page',
    ],

    'hints' => [
        'background_image' => 'Recommandé : 1920x1080px ou plus',
        'overlay_opacity' => 'Assombrit l\'image de fond pour une meilleure lisibilité du texte',
    ],

    'preview' => [
        'title' => 'Aperçu du bloc',
        'note' => 'Ceci est un aperçu utilisant la locale actuelle (:locale). Les styles peuvent varier selon votre implémentation frontend.',
        'not_available' => 'Aperçu du bloc non disponible. Type de bloc : :type',
        'no_data' => 'Aucune donnée de bloc disponible pour l\'aperçu.',
    ],
];
