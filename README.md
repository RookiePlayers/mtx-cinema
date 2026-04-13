# MTX Cinema

## Conceptual Guide

### Task / Motivation

So the task itself is quite simple, we want to create a system that does the following

#### Functional Requirement

1. Enables anyone to search a movie title.
2. View details about a movie
3. Allows authenticated users to save a movie
4. Allows authenticated users to explore movies they have saved
5. Allows authenticated users to explore movies other users have saved (for inspiration)
6. Allows authenticated users to delete/remove saved movies.
7. Allows authenticated users to edit movie details
8. Authenticated users can create a moive (bonus)

#### Non-Functional Requirement (Kinda)

1. Search must be fast and reliable, with a focus on consistency
2. Inputs must be validated and sanitized
3. Using the api alone for search can exert presure on the api easily hitting the rate limit, so we over reliance can cause searches to fail.
4. By supporting both API-sourced movies and user-created/edited entries, we've introduced an inconsistency in our data access paths. This should be addressed by unifying search through our internal database layer.

#### Thought process

So orignally my intention was to create a cron shecdule to siphon the movies from the api hourly and updated our internal database with the data, using a job-queue to process. The assumption made was that the api would present a route for retrieving a list of movies instead we only have search.
I've left this in, incase there was further actions required.

##### The Search

So the pivot was to focus entirely on Search. With the idea being populate our database as a side- effect of a search. *e.g user searches "bat", all results pertaining to "bat" will be saved and indexed in our DB*.
Speaking of search our search needs to be quick and reliable.so instead relying on wildcards i wanted to use FTS. Given, mysql has a built-in FTS module i wanted something with more control and familarity, in the past i would introduce a service for FTS like typesense, our even postgresFTS.
So introduced a system for Search indexing, which esentially creates a trigger on changes to the movies table, that run a procedure to create entries into `movie_search_index`. The result is blazing fast searches, amonst other benefits.
But we have a slight issue. In our search there is a bit of inconsistency happening. so when we searh we need a combination our the api results + our db result. meaning its shouldn't be one or the other but a malgamation.

The solution:

- search the local DB and the OMDB for the same query.
- combine the result as a set, dedupe my ImdbId(coninical)
- rank with a consistent scoring system based on other fields so ordering is predictable then finally return the results
we must ensure that we only fetch API page 1 during the request and persist additional results in the background, we must also cache the merged ordered result set so pagination stays stable

## Docker Startup

### Requirements

- Docker Desktop
- GNU Make

### Services

- App: PHP-FPM container
- Web: Nginx on `http://127.0.0.1:8000`
- MySQL: `127.0.0.1:3307`
- phpMyAdmin: `http://127.0.0.1:8080`
- Redis: `127.0.0.1:6379`

### First Start

Build and start the stack:

```bash
make rebuild
```

Install PHP dependencies inside the app container:

```bash
docker compose -f dockerfile-compose.yml exec app composer install
```

Generate the application key if needed:

```bash
docker compose -f dockerfile-compose.yml exec app php artisan key:generate
```

Run database migrations:

```bash
make migrate
```

### Daily Commands

Start containers:

```bash
make up
```

Stop containers:

```bash
make down
```

Build images:

```bash
make build
```

Rebuild from scratch and restart:

```bash
make rebuild
```

Follow app logs:

```bash
make logs
```

Run migrations:

```bash
make migrate
```

Destroy containers, volumes, and the Docker network:

```bash
make destroy
```

### Notes

- The app uses the Docker service names from `.env`, so Laravel connects to MySQL via `db` and Redis via `redis`.
- The custom Docker network `mtx_cinema_network` is created automatically by the `make build` and `make rebuild` targets.
