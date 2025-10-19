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
        } , $matches[0]);
    }

    function getQueryReplacementFromIdentifier(IdentifierInterface $replacement, OverrideCollection|null $overrides = null): string {
        if ($overrides !== null && $overrides->keyExists($replacement)) {
            return $overrides->getValue($replacement);
        }

        return $replacement instanceof BackedEnum ? $replacement->value : strtolower($replacement->name);
    }

    function collectPlaceholders(
        string                       $query,
        OverrideCollection|null      $overrides = null,
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
                $placeholderReplacements[] = new PlaceholderReplacement($placeholder, getQueryReplacementFromIdentifier($identifierOrPair, $overrides));
            }
        }

        return new PlaceholderReplacementCollection(...$placeholderReplacements);
    }

    function replacePlaceholders(
        string                       $query,
        OverrideCollection|null      $overrides = null,
        BaseNamespaceCollection|null $namespaces = null,
    ): string
    {
        $placeholderReplacements = collectPlaceholders($query, $overrides, $namespaces);

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
                $value = $parameters->getArrayValue($placeholder);
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

    function createMapFromQueryResult( array $data, string $query, OverrideCollection|null $overrides = null, BaseNamespaceCollection|null $namespaces = null): SplObjectStorage|MapCollection
    {
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
                        $queryReplacement = getQueryReplacementFromIdentifier($identifierOrPair, $overrides);

                        if(array_key_exists($queryReplacement, $row)) {
                            $map[$identifierOrPair] = $row[$queryReplacement];
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
                $queryReplacement = getQueryReplacementFromIdentifier($identifierOrPair, $overrides);

                if(array_key_exists($queryReplacement, $data)) {
                    $map[$identifierOrPair] = $data[$queryReplacement];
                }
            }
        }

        return $map;
    }

}

namespace Xwero\ComposableQueries\PDO {

    use PDO;
    use PDOException;
    use PDOStatement;
    use Xwero\ComposableQueries\BaseNamespaceCollection;
    use Xwero\ComposableQueries\Error;
    use Xwero\ComposableQueries\OverrideCollection;
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

    function getStatement(Connection $conn, string $query, QueryParametersCollection|null $parameters = null, OverrideCollection|null $overrides = null, BaseNamespaceCollection|null $namespaces = null): PDOStatement
    {
        $query = replacePlaceholders($query, $overrides, $namespaces);
        $statementParameters = [];

        if ($parameters !== null) {
            $statementParameters = collectQueryParameters($query, $parameters, $namespaces);
            $query = replaceParameters($query, $statementParameters);
        }

        $statement = $conn->connection->prepare($query);

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
    }

    function getOne(PDOStatement $statement, int $column = 0) : mixed
    {
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

    function getRow(PDOStatement $statement, int $pdoMode = PDO::FETCH_ASSOC) : mixed
    {
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

    function getAll(PDOStatement $statement, int $pdoMode = PDO::FETCH_ASSOC) : mixed
    {
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

    use Predis\Response\ServerException;
    use Xwero\ComposableQueries\BaseNamespaceCollection;
    use Xwero\ComposableQueries\Error;
    use Xwero\ComposableQueries\QueryParametersCollection;
    use function Xwero\ComposableQueries\addQueryParameters;
    use function Xwero\ComposableQueries\replacePlaceholders;

    function getStatement(string $query, QueryParametersCollection|null $parameters = null, BaseNamespaceCollection|null $namespaces = null): Command|Error
    {
        $query = replacePlaceholders($query, null, $namespaces);

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

    function executeStatement(Connection $connection, Command $command): Error|true
    {
        try {
            $connection->connection->{$command->name}($command->arguments ?? []);

            return true;
        }catch (ServerException $e) {
            return new Error($e);
        }
    }

    function getResult(Connection $connection, Command $command): string|int|array|Error
    {
        try {
            return $connection->connection->{$command->name}($command->arguments ?? []);
        }catch (ServerException $e) {
            return new Error($e);
        }
    }
}