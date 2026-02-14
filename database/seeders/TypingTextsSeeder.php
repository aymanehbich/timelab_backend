<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypingTextsSeeder extends Seeder
{
    public function run(): void
    {
        $texts = [

            // ─── BEGINNER ───────────────────────────────────────────────────────────
            [
                'level'      => 'beginner',
                'title'      => 'Qu\'est-ce que la Loi de Parkinson ?',
                'content'    => 'La Loi de Parkinson dit que le travail s\'étale pour remplir le temps disponible. Si tu as deux heures pour une tâche d\'une heure, tu prendras deux heures. La solution est simple : fixe-toi des délais courts. Une contrainte de temps force ton cerveau à se concentrer et à éliminer le superflu. Commence dès maintenant.',
                'word_count' => 57,
                'time_limit' => 90,
            ],
            [
                'level'      => 'beginner',
                'title'      => 'Le piège du temps infini',
                'content'    => 'Quand tu n\'as pas de deadline, tu procrastines. Ton cerveau sait qu\'il a tout le temps et reporte sans cesse. Donne-toi des limites courtes et claires. Une heure bien utilisée vaut mieux que quatre heures gaspillées. La contrainte est ton alliée, pas ton ennemie. Travaille vite, travaille bien.',
                'word_count' => 52,
                'time_limit' => 85,
            ],
            [
                'level'      => 'beginner',
                'title'      => 'La technique du timer',
                'content'    => 'Pose un timer avant de commencer chaque tâche. Décide combien de temps tu veux y consacrer, puis travaille sans distraction jusqu\'au signal. Cette méthode combat directement la Loi de Parkinson. Elle te force à prioriser l\'essentiel. Avec la pratique, tu seras étonné de ce que tu peux accomplir en peu de temps.',
                'word_count' => 55,
                'time_limit' => 90,
            ],

            // ─── INTERMEDIATE ────────────────────────────────────────────────────────
            [
                'level'      => 'intermediate',
                'title'      => 'Cyril Northcote Parkinson et sa loi',
                'content'    => 'En 1955, l\'historien britannique Cyril Northcote Parkinson publia un article satirique dans The Economist. Sa thèse : le travail s\'étale pour occuper tout le temps disponible pour son accomplissement. Ce qu\'il observait dans la bureaucratie s\'applique à tous les domaines de la vie. Plus on a de temps pour réaliser une tâche, plus on en prend. La solution consiste à imposer des contraintes artificielles. En réduisant volontairement le temps alloué, on force le cerveau à se concentrer sur l\'essentiel et à éliminer les distractions. Le résultat est souvent aussi bon, voire meilleur, que si on avait pris tout le temps voulu.',
                'word_count' => 113,
                'time_limit' => 150,
            ],
            [
                'level'      => 'intermediate',
                'title'      => 'Estimation du temps : l\'art de la précision',
                'content'    => 'La plupart des gens surestiment le temps nécessaire pour accomplir une tâche simple et sous-estiment le temps requis pour les projets complexes. Ce paradoxe est au cœur de la mauvaise gestion du temps. La Loi de Parkinson explique la première partie : si tu penses avoir trois heures, tu en prendras trois. Pour contrer ce phénomène, entraîne-toi à estimer précisément. Note ton estimation avant de commencer, mesure le temps réel, et compare. Avec la pratique, tes estimations s\'amélioreront et tu gagneras un contrôle précieux sur ton agenda. La productivité n\'est pas une question de talent, c\'est une compétence qui s\'apprend.',
                'word_count' => 118,
                'time_limit' => 155,
            ],
            [
                'level'      => 'intermediate',
                'title'      => 'La méthode Pomodoro contre Parkinson',
                'content'    => 'Francesco Cirillo a développé la technique Pomodoro dans les années 1980 pour lutter contre la procrastination. Le principe est simple : travaille 25 minutes sans interruption, puis fais une pause de 5 minutes. Cette approche crée des mini-contraintes de temps qui activent le cerveau de manière optimale. Elle répond directement au problème soulevé par la Loi de Parkinson. En découpant le travail en petits blocs chronométrés, on empêche la tâche de s\'étaler indéfiniment. De plus, la pause régulière maintient la fraîcheur mentale tout au long de la journée. Associe cette technique à des estimations précises pour maximiser ta productivité.',
                'word_count' => 112,
                'time_limit' => 148,
            ],

            // ─── EXPERT ─────────────────────────────────────────────────────────────
            [
                'level'      => 'expert',
                'title'      => 'L\'économie comportementale de la gestion du temps',
                'content'    => 'La Loi de Parkinson s\'inscrit dans un ensemble de biais cognitifs qui perturbent notre rapport au temps. Le planning fallacy, identifié par Kahneman et Tversky, nous pousse à sous-estimer systématiquement la durée des projets futurs tout en surestimant notre capacité à les accomplir. Combiné à l\'effet Zeigarnik — qui maintient les tâches inachevées en mémoire de travail — et au biais de présent — qui donne une valeur disproportionnée aux gratifications immédiates — ces phénomènes créent un environnement cognitif hostile à la productivité. La Loi de Parkinson vient amplifier ces distorsions : le temps disponible devient un signal de permissivité pour le cerveau, qui interprète l\'absence de contrainte comme une autorisation de différer l\'effort. La solution neurologique passe par la création délibérée de contraintes temporelles artificielles, qui activent le système limbique et mobilisent l\'attention sélective. En comprenant ces mécanismes, tu deviens architecte de ton propre environnement décisionnel.',
                'word_count' => 152,
                'time_limit' => 180,
            ],
            [
                'level'      => 'expert',
                'title'      => 'Deep Work et la Loi de Parkinson',
                'content'    => 'Cal Newport, dans son ouvrage Deep Work, défend l\'idée que la capacité à se concentrer profondément sur une tâche cognitive exigeante est devenue la compétence la plus précieuse de l\'économie du savoir. Cette thèse entre en résonance directe avec la Loi de Parkinson. Le travail superficiel — emails, réunions, multitâche — s\'étale naturellement pour remplir le temps disponible, tandis que le travail profond résiste à cette dilution par sa nature même. Newport propose de planifier chaque heure de sa journée de travail avec une intention claire, une durée définie et un objectif mesurable. Cette discipline temporelle n\'est pas une contrainte rigide mais un cadre libérateur : en sachant exactement ce qu\'on fait et pendant combien de temps, on libère son esprit des décisions répétées et de l\'anxiété de la procrastination. La combinaison du Deep Work et de la conscience de la Loi de Parkinson constitue un système complet de productivité intentionnelle.',
                'word_count' => 155,
                'time_limit' => 185,
            ],
            [
                'level'      => 'expert',
                'title'      => 'La contrainte comme moteur de créativité',
                'content'    => 'Un paradoxe fascinant de la psychologie de la créativité révèle que les contraintes temporelles — loin d\'étouffer l\'innovation — la stimulent. Des recherches menées à l\'université de Columbia montrent que les individus soumis à des contraintes de ressources, de temps ou de budget produisent des solutions plus originales que ceux disposant de ressources illimitées. Ce phénomène, appelé "creative constraint effect", s\'explique par l\'activation des circuits de résolution de problèmes dans le cortex préfrontal. La Loi de Parkinson, habituellement perçue comme un obstacle à l\'efficacité, révèle ici son potentiel inversé : si le travail s\'étale pour remplir le temps disponible, alors réduire ce temps force le cerveau à aller à l\'essentiel, à faire des choix, à innover. Les meilleurs créatifs et entrepreneurs utilisent cette logique consciemment : ils s\'imposent des sprints courts, des prototypes rapides, des décisions en temps limité. La contrainte, bien dosée, est le carburant de l\'excellence.',
                'word_count' => 158,
                'time_limit' => 190,
            ],
        ];

        foreach ($texts as $text) {
            $exists = DB::table('typing_texts')
                ->where('title', $text['title'])
                ->exists();

            if (!$exists) {
                DB::table('typing_texts')->insert(array_merge($text, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
