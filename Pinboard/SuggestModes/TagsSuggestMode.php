<?php namespace Statamic\Addons\Pinboard\SuggestModes;

// need to include these because they don't use namespaces
use PinboardAPI;
use PinboardException;
use PinboardException_ConnectionError;
use PinboardException_InvalidResponse;

use Statamic\Extend\Extensible;
use Statamic\Addons\Suggest\Modes\AbstractMode;

class TagsSuggestMode extends AbstractMode
{
    use Extensible;

    public function suggestions()
    {
        return $this->getTags()->map(function ($tag, $ignored) {
            return ['value' => $tag, 'text' => $tag];
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getTags()
    {
        $tags = array();
        $pinboard = new PinboardAPI(null, $this->getConfig('token'));

        try {
            $tags = $pinboard->get_tags();

        } catch (PinboardException_ConnectionError $ce) {
            // just ignore this
        } catch (PinboardException_InvalidResponse $ir) {
            // just ignore this
        } catch (PinboardException $e) {
            \Log::error($e->getMessage());
        }

        return collect($tags)->map(function ($tag, $ignored) {
            return (string)$tag;
        });
    }

}