<?php
namespace Laranix\Auth\User\Cage;

use Illuminate\Database\Eloquent\Model;

trait CreatesUserCage
{
    /**
     * @param array|Settings $values
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Auth\User\Cage\Cage
     */
    public function createUserCage($values) : Model
    {
        if (is_array($values)) {
            $values = new Settings($values);
        }

        $values->hasRequiredSettings();

        $config = $this->config ?? config();

        if ($config->get('laranixauth.cage.save_rendered', true)) {
            $rendered = markdown($values->reason);
        }

        return Cage::createNew([
            'level'             => $values->level,
            'area'              => $values->area,
            'length'            => $values->length,
            'reason'            => $values->reason,
            'reason_rendered'   => $rendered ?? null,
            'issuer_id'         => $values->issuer,
            'user_id'           => $values->user,
            'user_ipv4'         => $values->ipv4 ?? null,
        ]);
    }
}
