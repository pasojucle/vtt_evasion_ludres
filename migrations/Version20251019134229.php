<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use DateTime;
use App\Entity\Licence;
use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use App\Entity\Enum\LicenceStateEnum;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251019134229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence ADD state ENUM(\'draft\', \'trial_file_pending\', \'trial_file_submitted\', \'trial_file_received\', \'trial_completed\', \'yearly_file_pending\', \'yearly_file_submitted\', \'yearly_file_received\', \'yearly_file_registred\', \'cancelled\', \'expired\') DEFAULT \'draft\' NOT NULL COMMENT \'(DC2Type:LicenceState)\'');
    }

    public function postUp(Schema $schema): void
    {
        foreach($this->getStates() as $final => $states) {
            foreach($states as $status =>  $state) {
                $this->connection->executeQuery("UPDATE licence set state=:state WHERE status=:status AND final=:final", [
                    'state' => $state->value,
                    'status' => $status,
                    'final' => $final,
                ]);
            }
        }

        $currentSeason = $this->getCurrentSeason();
        If ($currentSeason) {
            $params = [
                'season' => $currentSeason,
                'trial_file_submitted' => LicenceStateEnum::TRIAL_FILE_SUBMITTED->value,
                'trial_file_received' => LicenceStateEnum::TRIAL_FILE_RECEIVED->value,
                'isPresent' => 1,
                'count' => 2,
            ];
            $userIds = $this->connection->fetchFirstColumn('SELECT u.id FROM `session` AS s INNER JOIN user AS u ON s.user_id = u.id INNER JOIN licence AS l ON l.user_id = u.id WHERE l.season = :season AND (l.state = :trial_file_submitted OR l.state = :trial_file_received) AND s.is_present = :isPresent GROUP BY u.id HAVING COUNT(s.id) > :count', $params);
            if (!empty($userIds)) {
                $this->connection->executeQuery(
                    'UPDATE licence SET state = :trial_completed WHERE user_id IN (:user_ids)',
                    [
                        'trial_completed' => LicenceStateEnum::TRIAL_COMPLETED->value,
                        'user_ids' => $userIds,
                    ],
                    [
                        'user_ids' => ArrayParameterType::INTEGER,
                    ]
                );
            }

            $params = [
                'registered' => LicenceStateEnum::YEARLY_FILE_REGISTRED->value,
                'receive' => LicenceStateEnum::YEARLY_FILE_RECEIVED->value,
                'season' => $currentSeason,
            ];
            $this->connection->executeQuery('UPDATE licence SET state=:registered WHERE state=:receive AND season < :season', $params);
        }
        $this->connection->executeQuery('ALTER TABLE licence DROP final, DROP status');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence ADD final TINYINT(1) DEFAULT 1 NOT NULL, ADD status INT NOT NULL');

        $this->addSql('ALTER TABLE licence DROP state');
    }

    private function getStates(): array
    {
        return [
            0 => [
                Licence::FILTER_IN_PROCESSING => LicenceStateEnum::TRIAL_FILE_PENDING,
                Licence::FILTER_WAITING_VALIDATE => LicenceStateEnum::TRIAL_FILE_SUBMITTED,
                Licence::FILTER_TESTING => LicenceStateEnum::TRIAL_FILE_RECEIVED,
            ],
            1 => [
                Licence::FILTER_IN_PROCESSING => LicenceStateEnum::YEARLY_FILE_PENDING,
                Licence::FILTER_WAITING_VALIDATE => LicenceStateEnum::YEARLY_FILE_SUBMITTED,
                Licence::FILTER_VALID => LicenceStateEnum::YEARLY_FILE_RECEIVED,
            ],
        ];
    }

    private function getCurrentSeason(): ?int
    {
        $today = new DateTime();
        $seasonStartAt = $this->connection->executeQuery('SELECT value FROM parameter WHERE name = \'SEASON_START_AT\'')->fetchOne();
        if ($seasonStartAt) {
            $seasonStartAt = json_decode($seasonStartAt, true);
            return ($seasonStartAt['month'] <= (int) $today->format('m') && $seasonStartAt['day'] <= (int) $today->format('d'))
            ? (int) $today->format('Y') + 1
            : (int) $today->format('Y');
        }

        return null;
    }
}
