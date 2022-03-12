<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Health;
use Doctrine\Common\Collections\Collection;

class HealthViewModel extends AbstractViewModel
{
    public ?Health $entity;

    public ?string $socialSecurityNumber;

    public ?string $mutualCompany;

    public ?string $mutualNumber;

    public ?string $bloodGroup;

    public ?string $tetanusBooster;

    public ?string $doctorName;

    public ?string $doctorAddress;

    public ?string $doctorTown;

    public ?string $doctorPhone;

    public ?string $phonePicto;

    public ?array $diseases;

    public ?string $medicalCertificateDate;

    public static function fromHealth(?Health $health)
    {
        $healthView = new self();
        if ($health) {
            $tetanusBoosterDate = $health->getTetanusBooster();
            $medicalCertificateDate = $health->getMedicalCertificateDate();
            $healthView->entity = $health;
            $healthView->socialSecurityNumber = $health->getSocialSecurityNumber();
            $healthView->mutualCompany = $health->getMutualCompany();
            $healthView->mutualNumber = $health->getMutualNumber();
            $healthView->bloodGroup = $health->getBloodGroup();
            $healthView->tetanusBooster = ($tetanusBoosterDate) ? $tetanusBoosterDate->format('d/m/Y') : null;
            $healthView->doctorName = $health->getDoctorName();
            $healthView->doctorAddress = $health->getDoctorAddress();
            $healthView->doctorTown = $health->getDoctorTown();
            $healthView->doctorPhone = $health->getDoctorPhone();
            $healthView->phonePicto = 'iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAC3HpUWHRSYXcgcHJvZmlsZSB0eXBlIGV4aWYAAHja7ZdNktwgDIX3nCJHQBJC4jjYQFVukOPnYdPu6Z7OVOVnkUWbMmBZPECfTM+E/uP7CN9wUZEYkprnknPElUoqXNHxeF5nSzEd9fmQ1jt6tIfrBcMkaOV8zH35V9j1PsCWP22P9mD70vEltF7cBGXOzOgsP19Cwqf9tpBQ1riaPmxn3eO2RTub5+dkCEZT6AkH7kISUfucReZNUtFm1CSJbxaWhFpEX8cuXN2n4F29p9jFuuzyGIoQ83LITzFadtLXsTsi9EDtPvPDC5Nris+xG83H6OfuasqIVA5rU7etHD04bgilHMMyiuFW9O0oBcWxxR3EGmhuKHugQoxoD0rUqNKgfrQ77Vhi4s6GlnlHxKfNxbjwDhgAMQsNNinSgjh47KAmMPO1FjrmLcd8OzlmbgRPJogRRnwq4ZXxT8olNMZMXaLoV6ywLp45jWVMcrOGF4DQWDHVI75HCR/yJn4AKyCoR5gdG6xxOyU2pXtuycFZ4KcxhXh+GmRtCSBEmFuxGBIQiJlEKVM0ZiNCHB18KlaOVOcNBEiVG4UBNiIZcJzn3BhjdPiy8mnG0QIQio/GgKZIBayUFPljyZFDVUVTUNWspq5Fa5acsuacLc8zqppYMrVsZm7FqosnV89u7l68Fi6CI0xLLhaKl1JqxaQV0hWjKzxq3XiTLW265c0238pWd6TPnnbd826772WvjZs0fP4tNwvNW2m1U0cq9dS1527de+l1INeGjDR05GHDRxn1oraoPlKjJ3JfU6NFbRJLh5/dqcFsdpOgeZzoZAZinAjEbRJAQvNkFp1S4kluMouF5yHFoEY64TSaxEAwdWIddLG7k/uSW9D0W9z4V+TCRPcvyIWJbpH7zO0FtVaPXxQ5AM2vcMY0ysDBBofulb3O36Q/bsPfCryF3kJvobfQW+gt9Bb6f4QG/njAv5rhJ8n4kT5PdD3IAAAABmJLR0QAEwAgAD4nLw1mAAAACXBIWXMAAA3XAAAN1wFCKJt4AAAAB3RJTUUH5QYOExgj295WAAAAAURJREFUOMut1TlKBGEQBeCv3XBBBBHELRYz1wuIqSCI4h0MFAwMPYBgJngHM8HQWBDRxFxEEVyDQRCXmTawRpphGKbHKSjo/qFfvVdV/+vEb7SiC4nGIsU7ignmsI7hfwLeYx8uUIzD/2QRFwlKwSzFR+QDXjGJzpxM/yrcYTNA+jGC4yiYh+nfwxnGKiouo5AHsCXzcQ+6KwBPcJ5nOlnAIYxm3ltDdlcjI0+jVztoj/NpXOK7UckJ1oKVAGqrUJGLYYpP7IbMFqzgpgqTUj1TLucjVoNdG5Zwha9Y3lNs4QjPQaImYIprLKIjhjOBg9jLySjUgxnsxSWoCVjCLTbQF/3txECVno6HgpqA5XzDIRYCuFrM46VewHI+hNwNTMXV7I0i29khZc2hnm34DPN4CrsqYBaDWXNoun011WCTZv8CfgAlecavwAWn+wAAAABJRU5ErkJggg==';
            $healthView->diseases = $healthView->getDiseases($health->getDiseases());
            $healthView->medicalCertificateDate = ($medicalCertificateDate) ? $medicalCertificateDate->format('d/m/Y') : null;
        }

        return $healthView;
    }

    public function getDiseases(Collection $allDiseases): array
    {
        $diseases = [];
        foreach ($allDiseases as $disease) {
            if (null !== $disease->getTitle() || null !== $disease->getCurentTreatment() || null !== $disease->getEmergencyTreatment()) {
                $diseases[$disease->getType()][] = DiseaseViewModel::fromDisease($disease);
            }
        }

        return $diseases;
    }
}
