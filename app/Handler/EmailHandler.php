<?php

namespace App\Handler;

use App\Models\Template;
use App\Models\ProposedSchool;
use App\Factory\MarketingEmailFactory;
use App\Jobs\ProcessMarketingEmail;
use Illuminate\Database\Eloquent\Builder;

class EmailHandler
{
    public static $emailService;

    public static $emailJob;

    public function __construct()
	{
        self::$emailService = new MarketingEmailFactory();
    }

    public static function init()
	{
        new static();
    }

    public static function buildCta()
    {
        $cta= [];

        if(request()->filled(['cta', 'cta_text']))
        {
            $cta['cta'] = request()->cta;
            $cta['text'] = request()->cta_text;
        }

        return $cta;
    }

    public static function preview()
    {
        self::init();

        $content = Template::where('id', request()->id)->first();
        
        return self::$emailService->emailClient->preview($content->content, self::buildCta());
    }

    public static function sendEmail()
    {
        //Let us fetch the contents of the template first.
        $content = Template::where('id', request()->template_id)->first();

        //We fetch possible recipients
        $to = ProposedSchool::whereIn('id', request()->recipients)
        ->whereDoesntHave('emailsSent', function (Builder $query) {
            $query->where('template_id', request()->template_id);
        })
        ->get()->toArray();
        
        if(!empty($content) and !empty($to))
        {
            ProcessMarketingEmail::dispatch(request()->template_id, $to, request()->subject, $content->content, self::buildCta());
        }
        else
        {
            abort(422,"It seems recipients have previously been sent similar email in the past. You can't send same email more than once to avoid spamming.");
        }
    }
}
