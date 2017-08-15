<?php
namespace Laranix\AntiSpam\Sequence;

use Laranix\AntiSpam\AntiSpam;

class Sequence extends AntiSpam
{
    /**
     * Session key.
     *
     * @var string
     */
    const SESSION_KEY = '__form_sequence_value';

    /**
     * Get view data.
     *
     * @param string $formId
     * @return array
     */
    protected function getViewData(?string $formId = null) : array
    {
        return [
            'sequence' => [
                'fieldName'    => $this->config->get('antispam.sequence.field_name', '__sequence_id'),
                'fieldValue'   => $this->createSequenceNumber(),
            ],
        ];
    }

    /**
     * Verify form request.
     *
     * @return bool
     */
    protected function verifyRequest() : bool
    {
        $fieldName = $this->config->get('antispam.sequence.field_name', '__sequence_id');

        if (!$this->request->has($fieldName)) {
            $this->redirectMessage = 'Form sequence ID is not set';

            return false;
        }

        $session = $this->request->session();

        $sequence = $session->get(self::SESSION_KEY);

        if ($sequence === null) {
            $this->redirectMessage = 'The form session ID does not exist, this could be due to session expiry, or submitting the form more than once';

            return false;
        }

        $totalKey = self::SESSION_KEY.'.total';

        $session->increment($totalKey, $sequence['add']);

        if (((int) $this->request->get($fieldName) + $sequence['add']) === $session->get($totalKey, -1)) {
            $session->forget(self::SESSION_KEY);

            return true;
        }

        $session->keep(self::SESSION_KEY);

        $this->redirectMessage = 'The form session ID does not match, this could be due to session expiry, or submitting the form more than once';

        return false;
    }

    /**
     * Generate sequence number and store in session.
     *
     * @return int
     */
    protected function createSequenceNumber() : int
    {
        $session = $this->request->session();

        $value = random_int(100, 10000);
        $add = random_int(10, 1000);

        $session->put(self::SESSION_KEY, [
            'value'     => $value,
            'add'       => $add,
            'total'     => $value,
            // TODO Url referer?
        ]);

        return $value;
    }
}
