<?php
namespace Laranix\Tracker;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Laranix\Support\Database\Model;
use Laranix\Tracker\Events\BatchCreated;

class Writer implements TrackWriter
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var int
     */
    protected $buffersize;

    /**
     * @var array
     */
    protected $buffer = [];

    /**
     * @var int
     */
    protected $buffercount = 0;

    /**
     * Writer constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Http\Request                $request
     */
    public function __construct(Config $config, Request $request)
    {
        $this->config = $config;
        $this->request = $request;

        $this->buffersize = (int) $this->config->get('tracker.buffer', 0);
    }

    /**
     * Registers a new track
     *
     * @param \Laranix\Tracker\Settings|array $settings
     */
    public function register($settings)
    {
        if ($this->buffersize === 0 || $this->buffersize === 1) {
            $this->write($settings);

            return;
        }

        $settings = $this->parseSettings($settings);

        $payload = $this->getPayload($settings);

        $this->buffer[] = $payload;

        ++$this->buffercount;

        if ($this->buffersize !== -1 && $this->buffercount >= $this->buffersize) {
            $this->flush();
        }
    }

    /**
     * Add a new track, allows for chaining
     *
     * @param \Laranix\Tracker\Settings|array $settings
     * @return $this
     */
    public function add($settings)
    {
        $this->register($settings);

        return $this;
    }

    /**
     * Writes registered tracks
     *
     * @param \Laranix\Tracker\Settings|array $settings
     * @return \Laranix\Support\Database\Model|\Laranix\Tracker\Tracker
     */
    public function write($settings) : Model
    {
        $settings = $this->parseSettings($settings);

        return Tracker::createNew($this->getPayload($settings));
    }

    /**
     * @param \Laranix\Tracker\Settings|array $settings
     * @return \Laranix\Tracker\Settings
     */
    public function parseSettings($settings) : Settings
    {
        if ($settings instanceof Settings) {
            return $settings;
        }

        if (is_array($settings)) {
            return new Settings($this->request, $settings);
        }

        throw new \InvalidArgumentException('Settings is not a supported type');
    }

    /**
     * Parse settings to array
     *
     * @param \Laranix\Tracker\Settings $settings
     * @return array
     */
    protected function getPayload(Settings $settings) : array
    {
        $settings->hasRequiredSettings();

        $now = Carbon::now()->toDateTimeString();

        if ($this->config->get('tracker.save_rendered', true) && $settings->data !== null) {
            $rendered = markdown($settings->data);
        }

        return [
            'user_id'               => $settings->user,
            'ipv4'                  => $settings->ipv4(),
            'user_agent'            => $settings->userAgent(),
            'request_method'        => $settings->requestMethod(),
            'request_url'           => $settings->requestUrl(),
            'tracker_type'          => strtolower($settings->type),
            'tracker_type_id'       => $settings->typeId,
            'tracker_item_id'       => $settings->itemId,
            'flag_level'            => (int) $settings->flagLevel,
            'trackable_type'        => $settings->trackType !== Tracker::TRACKER_ANY ? $settings->trackType : Tracker::TRACKER_TRAIL,
            'tracker_data'          => $settings->data,
            'tracker_data_rendered' => $rendered ?? null,
            'created_at'            => $now,
            'updated_at'            => $now,
        ];
    }

    /**
     * Flush buffer
     */
    public function flush()
    {
        if ($this->buffercount === 0) {
            return;
        }

        Tracker::insert($this->buffer);

        event(new BatchCreated($this->buffercount));

        $this->buffercount = 0;
        $this->buffer = [];
    }
}
