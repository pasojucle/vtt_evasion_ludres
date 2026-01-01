<?php

declare(strict_types=1);

namespace App\Entity\Enum;

use App\Entity\Enum\EnumTrait;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum LicenceStateEnum: string implements TranslatableInterface
{
    case DRAFT = 'draft';
    case TRIAL_FILE_PENDING = 'trial_file_pending';
    case TRIAL_FILE_SUBMITTED = 'trial_file_submitted';
    case TRIAL_FILE_RECEIVED = 'trial_file_received';
    case TRIAL_COMPLETED = 'trial_completed';

    case YEARLY_FILE_PENDING = 'yearly_file_pending';
    case YEARLY_FILE_SUBMITTED = 'yearly_file_submitted';
    case YEARLY_FILE_RECEIVED = 'yearly_file_received';
    case YEARLY_FILE_REGISTRED = 'yearly_file_registred';
    case EXPIRED = 'expired';

    case CANCELLED = 'cancelled';


    use EnumTrait;

    public function getClassName(): string
    {
        return match ($this) {
            self::DRAFT => 'alert-black',
            self::TRIAL_FILE_PENDING, self::YEARLY_FILE_PENDING, self::TRIAL_FILE_SUBMITTED, self::YEARLY_FILE_SUBMITTED => 'alert-warning',
            self::TRIAL_FILE_RECEIVED => 'success-test',
            self::YEARLY_FILE_RECEIVED, self::YEARLY_FILE_REGISTRED => 'success',
            default => 'alert-danger',
        };
    }

    public function isYearly(): bool
    {
        $yearlyStates = [
            self::YEARLY_FILE_PENDING,
            self::YEARLY_FILE_SUBMITTED,
            self::YEARLY_FILE_RECEIVED,
            self::YEARLY_FILE_REGISTRED,
            self::EXPIRED,
        ];
        
        return in_array($this, $yearlyStates);
    }

    public function toValidate(): bool
    {
        $validStates = [
            self::TRIAL_FILE_SUBMITTED,
            self::YEARLY_FILE_SUBMITTED,
        ];
        
        return in_array($this, $validStates);
    }

    public function toRegister(): bool
    {
        return self::YEARLY_FILE_RECEIVED === $this;
    }

    public function isPending(): bool
    {
        $pendingStates = [
            self::TRIAL_FILE_PENDING,
            self::YEARLY_FILE_PENDING,
        ];
        
        return in_array($this, $pendingStates);
    }

    public function isRegistered(): bool
    {
        $validStates = [
            self::TRIAL_FILE_SUBMITTED,
            self::TRIAL_FILE_RECEIVED,
            self::YEARLY_FILE_SUBMITTED,
            self::YEARLY_FILE_RECEIVED,
            self::YEARLY_FILE_REGISTRED,
        ];
        
        return in_array($this, $validStates);
    }

    public function isValid(): bool
    {
        $validStates = [
            self::TRIAL_FILE_RECEIVED,
            self::YEARLY_FILE_RECEIVED,
            self::YEARLY_FILE_REGISTRED,
        ];
        
        return in_array($this, $validStates);
    }


    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('licence.state.' . $this->value, locale: $locale);
    }
}
