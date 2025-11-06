<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries {

    use BackedEnum;
    use SplObjectStorage;

    function getIdentifierFromStrings(string $class, string $case): IdentifierInterface|null
    {
        if (!class_exists($class)) {
            return null;
        }

        if (!method_exists($class, 'cases')) {
            return null;
        }

        $cases = $class::cases();

        foreach ($cases as $c) {
            if ($c->name == $case) {
                return $c;
            }
        }

        return null;
    }

    function queryToIdentifierOrPairCollection(string $query, string $regex, BaseNamespaceCollection|null $namespaces = null): array
    {
        preg_match_all($regex, $query, $matches);

        if (count($matches) == 0) {
            return [];
        }

        $set = array_unique($matches[0]);
        // Order from the largest strings to the smallest strings to prevent similar named string errors during replacement.
        usort($set, fn ($a, $b) => strlen($b)<=> strlen($a));

        return array_map(function($item) use ($namespaces) {
            $pair = explode(':', substr($item, 1));

            if(class_exists($pair[0])){
                $replacement = getIdentifierFromStrings($pair[0], $pair[1]);
                if ($replacement instanceof IdentifierInterface) {
                    return [$item, $replacement];
                }
            }

            if ($namespaces instanceof BaseNamespaceCollection) {
                foreach ($namespaces->getAll() as $baseNamespace) {
                    $possibleClass = $baseNamespace . '\\' . $pair[0];
                    if (class_exists($possibleClass)) {
                        $replacement = getIdentifierFromStrings($possibleClass, $pair[1]);
                        if ($replacement instanceof IdentifierInterface) {
                            return [$item, $replacement];
                        }
                    }
                }
            }

            return [$item, $pair];
        } , $set);
    }

    function getReplacementFromIdentifier(IdentifierInterface $identifier): string {
        return $identifier instanceof BackedEnum ? $identifier->value : strtolower($identifier->name);
    }

    function collectPlaceholders(
        string                       $query,
        BaseNamespaceCollection|null $namespaces = null,
    ): PlaceholderReplacementCollection
    {
        $placeholders = queryToIdentifierOrPairCollection($query, "(~[A-Za-z1-9\\\]+:[A-Za-z1-9]+)", $namespaces);

        if(count($placeholders) === 0) {
            return new PlaceholderReplacementCollection();
        }

        $placeholderReplacements = [];

        foreach ($placeholders as $item) {
            [$placeholder, $identifierOrPair] = $item;

            if ($identifierOrPair instanceof IdentifierInterface) {
                $placeholderReplacements[] = new PlaceholderReplacement($placeholder, getReplacementFromIdentifier($identifierOrPair));
            }
        }

        return new PlaceholderReplacementCollection(...$placeholderReplacements);
    }

    function replacePlaceholders(
        string                       $query,
        BaseNamespaceCollection|null $namespaces = null,
    ): string
    {
        $placeholderReplacements = collectPlaceholders($query, $namespaces);

        return str_replace($placeholderReplacements->getPlaceholders(), $placeholderReplacements->getReplacements(), $query);
    }

    function collectQueryParameters(string $query, QueryParametersCollection $parameters, BaseNamespaceCollection|null $namespaces = null): array
    {
        $placeholders = queryToIdentifierOrPairCollection($query, "(:[A-Za-z1-9\\\]+:[A-Za-z1-9]+)", $namespaces);

        if (count($placeholders) == 0) {
            return [];
        }

        $placeholderReplacements = [];

        foreach ($placeholders as $item) {
            [$placeholder, $identifierOrPair] = $item;

            if (is_array($identifierOrPair) && $identifierOrPair[0] == 'Array') {
                $value = $parameters->getValue($placeholder);
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $placeholderReplacements[$placeholder . '_' . $k] = $v;
                    }
                }
            } elseif ($identifierOrPair instanceof IdentifierInterface && $parameters->keyExists($identifierOrPair)) {
                $placeholderReplacements[$placeholder] = $parameters->getValue($identifierOrPair);
            }
        }

        return $placeholderReplacements;
    }

    /*
     * This is the more dangerous parameter function.
     * Use only if the values are validated before adding them to the query.
     */
    function addQueryParameters(string $query, QueryParametersCollection $parameters, BaseNamespaceCollection|null $namespaces = null): string
    {
        $placeholders = queryToIdentifierOrPairCollection($query, "(:[A-Za-z1-9\\\]+:[A-Za-z1-9]+)", $namespaces);

        if (count($placeholders) == 0) {
            return $query;
        }

        $search = [];
        $replacements = [];

        foreach ($placeholders as $item) {
            [$placeholder, $identifierOrPair] = $item;

            if($identifierOrPair instanceof IdentifierInterface && $parameters->keyExists($identifierOrPair)) {
                $search[] = $placeholder;
                $replacements[] = $parameters->getValue($identifierOrPair);
            }
        }

        return str_replace($search, $replacements, $query);
    }

    function createMapFromQueryResult( array|Error $data, string $query, AliasCollection|null $aliases = null, BaseNamespaceCollection|null $namespaces = null): SplObjectStorage|MapCollection|Error
    {
        if($data instanceof Error) {
            return $data;
        }

        $map  = new SplObjectStorage();
        $placeholders = queryToIdentifierOrPairCollection($query, "(~[A-Za-z1-9\\\]+:[A-Za-z1-9]+)", $namespaces);

        if(count($placeholders) === 0) {
            foreach($data as $key => $item) {
                $map[$key] = $item;
            }

            return $map;
        }

        if(is_int(array_key_first($data))) {
            $mapCollection = new MapCollection();

            foreach($data as $row) {
                $map = new SplObjectStorage();

                foreach ($placeholders as $item) {
                    [$placeholder, $identifierOrPair] = $item;

                    if ($identifierOrPair instanceof IdentifierInterface) {
                        $queryReplacement = getReplacementFromIdentifier($identifierOrPair);

                        if(array_key_exists($queryReplacement, $row)) {
                            $map[$identifierOrPair] = $row[$queryReplacement];
                        }
                    }
                }

                if($aliases instanceof AliasCollection) {
                    foreach($data as $key => $value) {
                        if($identifier = $aliases->getIdentifier($key)) {
                            $map[$identifier] = $value;
                        }
                    }
                }

                $mapCollection->append($map);
            }

            return $mapCollection;
        }

        foreach ($placeholders as $item) {
            [$placeholder, $identifierOrPair] = $item;

            if ($identifierOrPair instanceof IdentifierInterface) {
                $queryReplacement = getReplacementFromIdentifier($identifierOrPair);

                if(array_key_exists($queryReplacement, $data)) {
                    $map[$identifierOrPair] = $data[$queryReplacement];
                }
            }
        }

        if($aliases instanceof AliasCollection) {
            foreach($data as $key => $value) {
                if($identifier = $aliases->getIdentifier($key)) {
                    $map[$identifier] = $value;
                }
            }
        }

        return $map;
    }

}

namespace Xwero\ComposableQueries\PDO {

    use Exception;
    use PDO;
    use PDOException;
    use PDOStatement;
    use Xwero\ComposableQueries\BaseNamespaceCollection;
    use Xwero\ComposableQueries\Error;
    use Xwero\ComposableQueries\QueryParametersCollection;
    use function Xwero\ComposableQueries\collectQueryParameters;
    use function Xwero\ComposableQueries\replacePlaceholders;

    /**
     * Replacing the possible backslashes and colon between the class and case prevents SQL errors.
     */
    function getPlaceholder(string $placeholder): string
    {
        if (str_contains($placeholder, '\\')) {
            $placeholder = str_replace('\\', '_', $placeholder);
        }

        return preg_replace('/([a-z0-9]):([A-Z])/', '$1_$2', $placeholder);
    }

    function replaceParameters(string $query, array $placeholderReplacements): string
    {
        $queryReplacements = [];

        foreach ($placeholderReplacements as $placeholder => $replacement) {
            $queryReplacements[$placeholder] = getPlaceholder($placeholder);
        }

        $arrayKeys = array_filter($placeholderReplacements, fn($key) => str_starts_with($key, ':Array'), ARRAY_FILTER_USE_KEY);

        if (count($arrayKeys) == 0) {
            return count($queryReplacements) == 0 ? $query : str_replace(array_keys($queryReplacements), array_values($queryReplacements), $query);
        }

        foreach (array_keys($arrayKeys) as $key) {
            $base = explode('_', $key)[0];
            $queryReplacements[$base][] = $key;
        }

        $queryReplacements = array_map(fn($item) =>  is_array($item) ? join(',', $item) : $item, $queryReplacements);

        return str_replace(array_keys($queryReplacements), array_values($queryReplacements), $query);
    }

    function getStatement(Connection $conn, string $query, QueryParametersCollection|null $parameters = null, BaseNamespaceCollection|null $namespaces = null): PDOStatement|Error
    {
        try {
            $query = replacePlaceholders($query, $namespaces);
            $statementParameters = [];

            if ($parameters !== null) {
                $statementParameters = collectQueryParameters($query, $parameters, $namespaces);
                $query = replaceParameters($query, $statementParameters);
            }

            $statement = $conn->client->prepare($query);

            if (count($statementParameters) > 0) {
                foreach ($statementParameters as $placeholder => $value) {
                    $type = match (true) {
                        is_string($value) => PDO::PARAM_STR,
                        is_bool($value) => PDO::PARAM_BOOL,
                        is_float($value) => PDO::PARAM_STR,
                        is_int($value) => PDO::PARAM_INT,
                    };

                    $statement->bindParam(getPlaceholder($placeholder), $value, $type);
                }
            }

            return $statement;
        } catch (Exception $e) {
            return new Error($e);
        }
    }

    function getOne(PDOStatement|Error $statement, int $column = 0) : mixed
    {
        if ($statement instanceof Error) {
            return $statement;
        }

        try {
            $statement->execute();
        } catch (PDOException $e) {
            return new Error($e);
        }

        try {
            return $statement->fetchColumn($column);
        } catch (PDOException $e) {
            return new Error($e);
        }
    }

    function getRow(PDOStatement|Error $statement, int $pdoMode = PDO::FETCH_ASSOC) : mixed
    {
        if ($statement instanceof Error) {
            return $statement;
        }

        try {
            $statement->execute();
        } catch (PDOException $e) {
            return new Error($e);
        }

        try {
            return $statement->fetch($pdoMode);
        } catch (PDOException $e) {
            return new Error($e);
        }
    }

    function getAll(PDOStatement|Error $statement, int $pdoMode = PDO::FETCH_ASSOC) : mixed
    {
        if ($statement instanceof Error) {
            return $statement;
        }

        try {
            $statement->execute();
        } catch (PDOException $e) {
            return new Error($e);
        }

        try {
            return $statement->fetchAll($pdoMode);
        } catch (PDOException $e) {
            return new Error($e);
        }
    }
}

namespace Xwero\ComposableQueries\Predis {

    use Exception;
    use Predis\Response\Status;
    use Predis\Transaction\MultiExec;
    use Xwero\ComposableQueries\BaseNamespaceCollection;
    use Xwero\ComposableQueries\Error;
    use Xwero\ComposableQueries\QueryParametersCollection;
    use function Xwero\ComposableQueries\addQueryParameters;
    use function Xwero\ComposableQueries\replacePlaceholders;

    function getStatement(string $query, QueryParametersCollection|null $parameters = null, BaseNamespaceCollection|null $namespaces = null): Command|Error
    {
        $query = replacePlaceholders($query, $namespaces);

        if($parameters !== null) {
            $query = addQueryParameters($query, $parameters, $namespaces);
        }

        $rawCommand = explode(' ', preg_replace('/\s+/', ' ', trim($query) ) );

        if(count($rawCommand) < 2) {
          return new Error(new InvalidCommand('A command needs at least a command and an argument.'));
        }

        $possibleCommand =  strtoupper(array_shift($rawCommand));
        $predisNamespace = 'Predis\Command\Redis';

        if(class_exists($predisNamespace . '\\' . $possibleCommand)) {
            return new Command(strtolower($possibleCommand), $rawCommand);
        }

        $subDirectories = [
          'AbstractCommand',
          'BloomFilter',
          'CountMinSketch',
          'CuckooFilter',
          'Json',
          'Search',
          'TDigest',
          'TimeSeries',
          'TopK'
        ];

        foreach ($subDirectories as $subDirectory) {
            if(class_exists($predisNamespace . '\\' . $subDirectory . '\\' . $possibleCommand)) {
                return new Command(strtolower($possibleCommand), $rawCommand);
            }
        }

        return new Error(new InvalidCommand("The command $possibleCommand does not exists."));
    }

    function executeCommand(Connection|MultiExec $connection, Command|Error $command): Error|true|string|array|int
    {
        if($command instanceof Error) {
            return $command;
        }

        try {
            if($connection instanceof MultiExec) {
                $connection->{$command->name}(...$command->arguments);

                return true;
            }

            $result = $connection->client->{$command->name}(...$command->arguments);

            if($result instanceof Status) {
                if($result->getPayload() == 'OK' ) {
                    return true;
                }

                return new Error(new \RedisException('Command failure with message: ' . $result->getPayload()));
            }

            return $result;
        }catch (Exception $e) {
            return new Error($e);
        }
    }

    function executeTransaction(Connection $connection, Command ...$commands): array|Error
    {
        try {
            return $connection->client->transaction(function ($trans) use ($commands) {
                foreach ($commands as $command) {
                    executeCommand($trans, $command);
                }
            });
        } catch (Exception $e) {
            return new Error($e);
        }
    }
}

namespace Xwero\ComposableQueries\MongoDb {

    use Exception;
    use InvalidArgumentException;
    use MongoDB\InsertManyResult;
    use MongoDB\InsertOneResult;
    use Xwero\ComposableQueries\BaseNamespaceCollection;
    use Xwero\ComposableQueries\Error;
    use Xwero\ComposableQueries\JSONException;
    use Xwero\ComposableQueries\QueryParametersCollection;
    use function Xwero\ComposableQueries\addQueryParameters;
    use function Xwero\ComposableQueries\getReplacementFromIdentifier;
    use function Xwero\ComposableQueries\replacePlaceholders;

    function buildDocument(DocumentBranch ...$branches): array
    {
        $insertByKeys = function(array $keys, $value, array &$dest)
        {
            $ptr = &$dest;                     // work on a reference to the root array

            foreach (array_slice($keys, 0, -1) as $k) {
                if (!isset($ptr[$k]) || !is_array($ptr[$k])) {
                    $ptr[$k] = [];             // initialise missing branch
                }
                $ptr = &$ptr[$k];              // descend one level
            }

            $lastKey = end($keys);
            $ptr[$lastKey] = $value;
        };

        $document = [];

        foreach ($branches as $branch) {
            $value = $branch->value;

            if(is_array($value) && array_any($value, fn($v) => $v instanceof DocumentBranch)) {
                $value = buildDocument(...$value);
            }

            if($branch->parents === null) {
                $document[getReplacementFromIdentifier($branch->id)] = $value;
                continue;
            }

            if(is_string($branch->parents) || is_int($branch->parents)) {
                $parent = $branch->parents;

                if(is_string($parent) && $parent == '*') {
                    $document[][getReplacementFromIdentifier($branch->id)] = $value;
                    continue;
                }

                $document[$branch->parents][getReplacementFromIdentifier($branch->id)] = $value;
                continue;
            }

            $insertByKeys($branch->parents, $value, $document);
        }

        return $document;
    }

    function getStatement(string $query, QueryParametersCollection|null $parameters = null, BaseNamespaceCollection|null $namespaces = null): array|Error
    {
        try {
            $query = replacePlaceholders($query,  $namespaces);

            if($parameters !== null) {
                $query = addQueryParameters($query, $parameters, $namespaces);
            }

            if(! json_validate($query)) {
                return new Error(new JsonException('Statement error: '));
            }

            return json_decode($query, true);
        }catch (Exception $e) {
            return new Error($e);
        }
    }

    function insert(Connection $connection, string $collection, array ...$documents): Error|InsertManyResult|InsertOneResult
    {
        if(count($documents) === 0) {
            return new Error(new InvalidArgumentException('No documents were added.'));
        }

        if(count($documents) === 1) {
            $document = $documents[0];

            try {
                return $connection->databaseClient->getCollection($collection)->insertOne($document);
            } catch (Exception $e) {
                return new Error($e);
            }
        }

        try {
            return $connection->databaseClient->getCollection($collection)->insertMany($documents);
        } catch (Exception $e) {
            return new Error($e);
        }
    }

    function getOne(Connection$connection, string $collection, array|Error $statement): array|Error
    {
        if($statement instanceof Error) {
            return $statement;
        }

        try {
            return $connection->databaseClient->getCollection($collection)->findOne($statement)->getArrayCopy();
        } catch (Exception $e) {
            return new Error($e);
        }
    }
}