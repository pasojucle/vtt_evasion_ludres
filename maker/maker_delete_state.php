<?php

declare(strict_types=1);

// 📋 Liste des 16 entités à générer (Exemples à adapter avec tes vrais noms d'entités)
$entities = [
    // ['className' => 'BoardRole', 'label' => 'Etes vous certain de supprimer le role %s', 'field' => 'getName()'],
    // ['className' => 'Category', 'label' => 'Etes vous certain de supprimer la catégorie %s', 'field' => 'getName()'],
    ['className' => 'Cluster', 'label' => 'Etes vous certain de supprimer l\'évaluation <b>%s</b> ?', 'field' => 'getContent()'],
    // ['className' => 'Documentation', 'label' => 'Etes vous certain de supprimer la documentation %s', 'field' => 'getName()'],
    // ['className' => 'Level', 'label' => 'Etes vous certain de supprimer le niveau %s', 'field' => 'getTitle()'],
    // ['className' => 'Licence', 'label' => 'Etes vous certain de supprimer l\'inscription de <b>%s</b> ?', 'field' => 'getMember()->getIdentity()->getFullName()'],
    // ['className' => 'Link', 'label' => 'Etes vous certain de supprimer le lien  <b>%s</b> ?', 'field' => 'getTitle()'],
    // ['className' => 'Message', 'label' => 'Etes vous certain de supprimer le message  <b>%s</b> ?', 'field' => 'getLabel'],
    // ['className' => 'Product', 'label' => 'Etes vous certain de supprimer l\'article <b>%s</b> ?', 'field' => 'getName()'],
    // ['className' => 'RegistrationStep', 'label' => 'Etes vous certain de supprimer l\'étape <b>%s</b> ?', 'field' => 'getTitle()'],
    // ['className' => 'SkillCategory', 'label' => 'Etes vous certain de supprimer la compétence <b>%s</b> ?', 'field' => 'getName()'],
    // ['className' => 'Skill', 'label' => 'Etes vous certain de supprimer la compétence <b>%s</b> ?', 'field' => 'getContent()'],
    // ['className' => 'SlideshowDirectory', 'label' => 'Etes vous certain de supprimer le répetroire <b>%s</b> ?', 'field' => 'getName()'],
    // ['className' => 'SlideshowImage', 'label' => 'Etes vous certain de supprimer l\'image <b>%s</b> ?', 'field' => 'getFilename()'],
    // ['className' => 'Summary', 'label' => 'EEtes vous certain de supprimer l\'actualité <b>%s</b> ?', 'field' => 'getTitle()'],
    // ['className' => 'Member', 'label' => 'Etes vous certain de supprimer l\'utilisateur <b>%s</b> ?', 'field' => 'getLicenceNumber()'],
];

// 📁 Configuration des chemins de base
$basePath = dirname(__DIR__) . '/src/State/';

foreach ($entities as $entity) {
    $className = $entity['className'];
    $label = $entity['label'];
    $field = $entity['field'];
    
    // Définition des dossiers cibles
    $providerDir = $basePath . $className . '/Provider/';
    $processorDir = $basePath . $className . '/Processor/';
    
    // Création des dossiers s'ils n'existent pas
    if (!is_dir($providerDir)) {
        mkdir($providerDir, 0777, true);
    }
    if (!is_dir($processorDir)) {
        mkdir($processorDir, 0777, true);
    }

    // 📄 1. Génération du DeleteProvider
    $providerContent = <<<PHP
<?php

declare(strict_types=1);

namespace App\State\\{$className}\Provider;

use App\Dto\DialogModalDto;
use App\Entity\\{$className};
use App\Mapper\DestructiveModalMapper;

class {$className}DeleteProvider
{
    public function __construct(
        private DestructiveModalMapper \$destructiveModalMapper,
    )
    {

    }
    public function mapToView({$className}  \$entity): DialogModalDto
    {

        return \$this->destructiveModalMapper->mapToView(sprintf('{$label}', \$entity->{$field}));
    }
}
PHP;

    // 📄 2. Génération du DeleteProcessor
    $processorContent = <<<PHP
<?php

declare(strict_types=1);

namespace App\State\\{$className}\Processor;

use App\Entity\\{$className};
use Doctrine\ORM\EntityManagerInterface;

class {$className}DeleteProcessor
{
    public function __construct(
        private EntityManagerInterface \$entityManager
    ) {}

    public function process({$className} \$entity): void
    {
        \$this->entityManager->remove(\$entity);
        \$this->entityManager->flush();
    }
}
PHP;

    // ✍️ Écriture des fichiers sur le disque
    file_put_contents($providerDir . "{$className}DeleteProvider.php", $providerContent);
    file_put_contents($processorDir . "{$className}DeleteProcessor.php", $processorContent);

    echo "⚙️  Généré : {$className}DeleteProvider et {$className}DeleteProcessor\n";
}

echo "✅ Terminé ! Les fichiers ont été créés avec succès.\n";