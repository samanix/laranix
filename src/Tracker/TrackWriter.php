<?php
namespace Laranix\Tracker;

interface TrackWriter
{
    /**
     * Registers a new track
     *
     * @param mixed $settings
     */
    public function register($settings);

    /**
     * Writes registered tracks
     *
     * @param mixed $settings
     */
    public function write($settings);

    /**
     * @param mixed $settings
     */
    public function parseSettings($settings);

    /**
     * Flush buffer
     */
    public function flush();
}
