<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EisenhowerTask;
use App\Models\EisenhowerAttempt;

class EisenhowerTaskSeeder extends Seeder
{
    public function run()
    {
        // Vider les tables avant de re-seeder
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        EisenhowerAttempt::truncate();
        EisenhowerTask::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $tasks = [
            // =========================================================
            // ===== NIVEAU 1 : Débutant (15 tâches) ==================
            // =========================================================

            // Q1 : Urgent + Important (4 tâches)
            [
                'title' => 'Terminer le rapport annuel',
                'description' => 'Le rapport doit être soumis avant la fin de la journée',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 1,
                'explanation' => 'Cette tâche est urgente et importante car elle a un impact direct sur votre travail et a une deadline aujourd\'hui.',
            ],
            [
                'title' => 'Corriger le bug de connexion',
                'description' => 'Les utilisateurs ne peuvent pas se connecter à l\'application',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 1,
                'explanation' => 'Le bug empêche les utilisateurs d\'utiliser le produit (urgent) et affecte la qualité globale (important).',
            ],
            [
                'title' => 'Préparer les documents pour la réunion',
                'description' => 'Réunir tous les documents nécessaires pour la réunion de l\'après-midi',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 1,
                'explanation' => 'Deadline immédiate et essentiel pour que la réunion soit productive.',
            ],
            [
                'title' => 'Éteindre un incendie dans le serveur',
                'description' => 'Le serveur principal est en panne, les clients ne peuvent plus accéder au site',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 1,
                'explanation' => 'Panne critique qui affecte tous les utilisateurs (urgent) et impacte le chiffre d\'affaires (important).',
            ],

            // Q2 : Pas Urgent + Important (4 tâches)
            [
                'title' => 'Faire du sport',
                'description' => 'Aller courir ou faire une séance de musculation',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 1,
                'explanation' => 'Important pour la santé et le bien-être, mais pas urgent. Planifiez-le.',
            ],
            [
                'title' => 'Lire un livre sur la gestion du temps',
                'description' => 'Améliorer ses compétences en productivité',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 1,
                'explanation' => 'Pas de deadline immédiate mais améliore vos compétences à long terme.',
            ],
            [
                'title' => 'Apprendre une nouvelle langue',
                'description' => 'Suivre des cours d\'anglais ou d\'espagnol en ligne',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 1,
                'explanation' => 'Pas de deadline (pas urgent), mais enrichir ses compétences linguistiques est un investissement important pour l\'avenir.',
            ],
            [
                'title' => 'Préparer son CV',
                'description' => 'Mettre à jour son CV avec les dernières expériences',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 1,
                'explanation' => 'Pas de deadline immédiate, mais avoir un CV à jour est important pour votre carrière.',
            ],

            // Q3 : Urgent + Pas Important (3 tâches)
            [
                'title' => 'Répondre aux emails non critiques',
                'description' => 'Emails qui nécessitent une réponse rapide mais sans impact sur vos objectifs',
                'is_urgent' => true,
                'is_important' => false,
                'quadrant' => 3,
                'level' => 1,
                'explanation' => 'Urgent pour les autres, mais pas important pour vos priorités. Peut être délégué.',
            ],
            [
                'title' => 'Répondre au téléphone pour un sondage',
                'description' => 'Un appel téléphonique pour répondre à un sondage commercial',
                'is_urgent' => true,
                'is_important' => false,
                'quadrant' => 3,
                'level' => 1,
                'explanation' => 'L\'appel est en cours (urgent), mais un sondage commercial n\'a aucun impact sur vos objectifs (pas important).',
            ],
            [
                'title' => 'Signer un accusé de réception pour un colis',
                'description' => 'Le livreur attend en bas pour un colis non prioritaire',
                'is_urgent' => true,
                'is_important' => false,
                'quadrant' => 3,
                'level' => 1,
                'explanation' => 'Le livreur attend maintenant (urgent), mais le colis n\'est pas critique (pas important). Demandez à quelqu\'un d\'autre si possible.',
            ],

            // Q4 : Pas Urgent + Pas Important (4 tâches)
            [
                'title' => 'Scroller sur les réseaux sociaux',
                'description' => 'Parcourir Instagram ou TikTok sans but précis',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 1,
                'explanation' => 'Ni urgent ni important. Activité distractive à limiter.',
            ],
            [
                'title' => 'Regarder des vidéos YouTube divertissantes',
                'description' => 'Regarder des vidéos pour passer le temps',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 1,
                'explanation' => 'Ni urgent ni important, cela ne contribue pas à vos objectifs.',
            ],
            [
                'title' => 'Jouer à un jeu vidéo',
                'description' => 'Lancer une partie de jeu vidéo pendant les heures de travail',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 1,
                'explanation' => 'Aucune deadline et aucun impact sur vos objectifs. Pure distraction à éliminer.',
            ],
            [
                'title' => 'Regarder les stories de ses amis',
                'description' => 'Passer du temps à regarder les stories Snapchat et Instagram',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 1,
                'explanation' => 'Ni urgent ni important. Cela ne vous rapproche d\'aucun objectif.',
            ],

            // =========================================================
            // ===== NIVEAU 2 : Intermédiaire (15 tâches) =============
            // =========================================================

            // Q1 : Urgent + Important (4 tâches)
            [
                'title' => 'Rendez-vous médical',
                'description' => 'Consultation prévue aujourd\'hui à 15h',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 2,
                'explanation' => 'Urgent car c\'est aujourd\'hui et important pour votre santé.',
            ],
            [
                'title' => 'Livrer le projet client',
                'description' => 'Deadline aujourd\'hui pour un projet critique',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 2,
                'explanation' => 'Impact direct sur la satisfaction client et la performance professionnelle.',
            ],
            [
                'title' => 'Résoudre un conflit dans l\'équipe',
                'description' => 'Deux membres de l\'équipe sont en désaccord et cela bloque le projet',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 2,
                'explanation' => 'Le conflit bloque le travail maintenant (urgent) et affecte la dynamique d\'équipe (important).',
            ],
            [
                'title' => 'Payer la facture avant la pénalité',
                'description' => 'La facture d\'électricité doit être payée aujourd\'hui sinon il y aura des frais de retard',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 2,
                'explanation' => 'La deadline est aujourd\'hui (urgent) et éviter les pénalités financières est important.',
            ],

            // Q2 : Pas Urgent + Important (4 tâches)
            [
                'title' => 'Préparer la présentation client',
                'description' => 'Slides pour la réunion de la semaine prochaine',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 2,
                'explanation' => 'Important pour les objectifs, planifiez-le dans votre agenda.',
            ],
            [
                'title' => 'Lire un article technique',
                'description' => 'Améliorer vos compétences dans votre domaine',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 2,
                'explanation' => 'Pas urgent mais enrichit vos compétences à long terme.',
            ],
            [
                'title' => 'Établir un budget mensuel',
                'description' => 'Planifier ses dépenses pour le mois prochain',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 2,
                'explanation' => 'Pas de deadline immédiate, mais gérer ses finances est essentiel pour la stabilité à long terme.',
            ],
            [
                'title' => 'Suivre une formation en ligne',
                'description' => 'Commencer un cours sur une compétence recherchée dans votre secteur',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 2,
                'explanation' => 'Pas urgent, mais investir dans vos compétences est important pour votre évolution professionnelle.',
            ],

            // Q3 : Urgent + Pas Important (3 tâches)
            [
                'title' => 'Appeler le fournisseur',
                'description' => 'Commande urgente mais sans impact stratégique',
                'is_urgent' => true,
                'is_important' => false,
                'quadrant' => 3,
                'level' => 2,
                'explanation' => 'Urgent mais peu important pour vos objectifs principaux. Déléguez.',
            ],
            [
                'title' => 'Participer à un appel de groupe non essentiel',
                'description' => 'Un collègue organise un appel pour discuter d\'un sujet secondaire',
                'is_urgent' => true,
                'is_important' => false,
                'quadrant' => 3,
                'level' => 2,
                'explanation' => 'L\'appel est maintenant (urgent), mais le sujet ne concerne pas vos priorités (pas important).',
            ],
            [
                'title' => 'Imprimer des documents pour un collègue',
                'description' => 'Un collègue a besoin de documents imprimés rapidement',
                'is_urgent' => true,
                'is_important' => false,
                'quadrant' => 3,
                'level' => 2,
                'explanation' => 'La demande est immédiate (urgent), mais ce n\'est pas votre responsabilité principale (pas important). Déléguez.',
            ],

            // Q4 : Pas Urgent + Pas Important (4 tâches)
            [
                'title' => 'Ranger le bureau',
                'description' => 'Nettoyer votre espace de travail sans impact immédiat',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 2,
                'explanation' => 'Ni urgent ni important, à faire pendant le temps libre.',
            ],
            [
                'title' => 'Changer le fond d\'écran de son ordinateur',
                'description' => 'Chercher un nouveau fond d\'écran esthétique',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 2,
                'explanation' => 'Aucune deadline et aucun impact. C\'est de la procrastination déguisée.',
            ],
            [
                'title' => 'Lire les commentaires sous un article',
                'description' => 'Parcourir les discussions en ligne sans but précis',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 2,
                'explanation' => 'Ni urgent ni important. Perte de temps qui n\'apporte rien à vos objectifs.',
            ],
            [
                'title' => 'Comparer les prix d\'un gadget en ligne',
                'description' => 'Passer du temps à chercher le meilleur prix pour un accessoire non nécessaire',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 2,
                'explanation' => 'Pas de deadline et achat non nécessaire. Distraction à éliminer.',
            ],

            // =========================================================
            // ===== NIVEAU 3 : Avancé (15 tâches) ====================
            // =========================================================

            // Q1 : Urgent + Important (3 tâches)
            [
                'title' => 'Gérer une plainte client majeure',
                'description' => 'Un client important menace de résilier son contrat si le problème n\'est pas résolu aujourd\'hui',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 3,
                'explanation' => 'Le client attend une réponse immédiate (urgent) et perdre ce client aurait un impact financier majeur (important).',
            ],
            [
                'title' => 'Préparer la soutenance de mémoire',
                'description' => 'La soutenance est demain et les slides ne sont pas prêts',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 3,
                'explanation' => 'Deadline demain (urgent) et la soutenance détermine votre diplôme (important). Priorité absolue.',
            ],
            [
                'title' => 'Réparer une fuite d\'eau à la maison',
                'description' => 'Une fuite d\'eau cause des dégâts dans la cuisine',
                'is_urgent' => true,
                'is_important' => true,
                'quadrant' => 1,
                'level' => 3,
                'explanation' => 'La fuite cause des dégâts maintenant (urgent) et peut endommager la maison (important). À traiter immédiatement.',
            ],

            // Q2 : Pas Urgent + Important (4 tâches)
            [
                'title' => 'Planifier la semaine',
                'description' => 'Définir les priorités et objectifs pour la semaine',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 3,
                'explanation' => 'Pas urgent mais essentiel pour organiser votre travail efficacement. Beaucoup négligent cette tâche et perdent en productivité.',
            ],
            [
                'title' => 'Sauvegarder les fichiers critiques',
                'description' => 'Créer des copies de sécurité pour éviter toute perte de données',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 3,
                'explanation' => 'Important à long terme, pas urgent immédiatement. On ignore cette tâche jusqu\'au jour où on perd tout.',
            ],
            [
                'title' => 'Construire son réseau professionnel',
                'description' => 'Contacter d\'anciens collègues et participer à des événements de networking',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 3,
                'explanation' => 'Pas urgent, mais le réseau est crucial pour les opportunités futures. Souvent confondu avec Q4 car le bénéfice n\'est pas immédiat.',
            ],
            [
                'title' => 'Rédiger un testament ou une procuration',
                'description' => 'Mettre en ordre ses documents juridiques personnels',
                'is_urgent' => false,
                'is_important' => true,
                'quadrant' => 2,
                'level' => 3,
                'explanation' => 'Aucune urgence, mais extrêmement important pour protéger vos proches. Tâche souvent repoussée indéfiniment.',
            ],

            // Q3 : Urgent + Pas Important (4 tâches)
            [
                'title' => 'Répondre à une demande instantanée d\'un collègue',
                'description' => 'Assistance pour une tâche non critique',
                'is_urgent' => true,
                'is_important' => false,
                'quadrant' => 3,
                'level' => 3,
                'explanation' => 'Urgent pour les autres mais pas important pour vos objectifs clés. Ça semble important car quelqu\'un vous sollicite.',
            ],
            [
                'title' => 'Assister à une réunion d\'information générale',
                'description' => 'Réunion obligatoire sur les nouvelles politiques RH de l\'entreprise',
                'is_urgent' => true,
                'is_important' => false,
                'quadrant' => 3,
                'level' => 3,
                'explanation' => 'La réunion est maintenant (urgent), mais le contenu ne concerne pas directement vos objectifs (pas important). Souvent confondu avec Q1.',
            ],
            [
                'title' => 'Remplir un formulaire administratif',
                'description' => 'Formulaire de remboursement de frais à soumettre avant ce soir',
                'is_urgent' => true,
                'is_important' => false,
                'quadrant' => 3,
                'level' => 3,
                'explanation' => 'La deadline est aujourd\'hui (urgent), mais c\'est une tâche administrative sans impact stratégique (pas important). Déléguez si possible.',
            ],
            [
                'title' => 'Répondre à un message de groupe WhatsApp',
                'description' => 'Le groupe attend votre réponse pour organiser un événement social',
                'is_urgent' => true,
                'is_important' => false,
                'quadrant' => 3,
                'level' => 3,
                'explanation' => 'Les gens attendent votre réponse maintenant (urgent), mais l\'événement social n\'impacte pas vos objectifs (pas important). Facile à confondre avec Q1.',
            ],

            // Q4 : Pas Urgent + Pas Important (4 tâches)
            [
                'title' => 'Réorganiser les icônes du téléphone',
                'description' => 'Perdre du temps à déplacer des applications',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 3,
                'explanation' => 'Ni urgent ni important, distraction inutile. Donne l\'illusion d\'être productif.',
            ],
            [
                'title' => 'Regarder les avis sur un restaurant',
                'description' => 'Passer 30 minutes à lire les avis pour un restaurant où vous n\'irez pas cette semaine',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 3,
                'explanation' => 'Aucune deadline et aucun impact. Parfois confondu avec Q2 (planification), mais c\'est de la procrastination.',
            ],
            [
                'title' => 'Trier ses vieilles photos',
                'description' => 'Passer du temps à organiser les photos de son téléphone',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 3,
                'explanation' => 'Pas urgent et pas important pour vos objectifs actuels. Peut sembler utile mais c\'est une distraction.',
            ],
            [
                'title' => 'Customiser son profil LinkedIn',
                'description' => 'Changer la bannière et réorganiser les sections de son profil',
                'is_urgent' => false,
                'is_important' => false,
                'quadrant' => 4,
                'level' => 3,
                'explanation' => 'Souvent confondu avec Q2 (développement professionnel), mais changer une bannière n\'a aucun impact réel sur votre carrière.',
            ],
        ];

        foreach ($tasks as $task) {
            EisenhowerTask::create($task);
        }
    }
}
