<?php

namespace App\Console\Commands;

use App\Movie;
use DiDom\Document;
use Illuminate\Console\Command;

class ParseMovieCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:imdb {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //Create link from id
        $link = 'https://www.imdb.com/title/' . $this->option('id');

        //Get document for parsing
        $document = new Document($link, true);

        //Get title
        $title = $document->find('h1')[0]->text();
        $this->info('Title: ' . $title);

        //Get poster
        $poster = $document->find('div.poster>a>img')[0]->attr('src');
        $this->info('Poster: ' . $poster);

        //Get categories as string
        $categories = '';
        foreach ($document->find('div.subtext>a:not([title=\'See more release dates\'])') as $category) {
            $categories = $categories . $category->text() . ', ';
        }
        $this->info('Categories: ' . $categories);

        //Get release date
        $releaseDate = $document->find('div.subtext>a[title=\'See more release dates\']')[0]->text();
        $this->info('Release date: ' . $releaseDate);

        //Get rating
        $rating = $document->first('span[itemprop="ratingValue"]');
        //If film has no rating - it's null
        if ($rating) $rating = $rating->text();

        $this->info('Rating: ' . $rating . '/10');

        //Get Director
        $director = $document->first('div.credit_summary_item>a')->text();
        $this->info('Director: ' . $director);

        //Store to DB
        //No reason to have a repository in this project, so using model directly
        Movie::create(
            [
                'title' => $title,
                'cover_image' => $poster ? $poster : null,
                'release_date' => $releaseDate,
                'rating' => $rating ? $rating . '/10' : null,
                'category' => $categories,
                'director' => $director
            ]
        );
    }
}
