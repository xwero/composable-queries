<?php

declare(strict_types=1);

namespace Xwero\ComposableQueries {

    use BackedEnum;
    use SplObjectStorage;

    function getReplacementFromStrings(string $class, string $case): IdentifierInterface|null
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

    function collectPlaceholders(
        string                       $query,
        OverrideCollection|null      $overrides = null,
        BaseNamespaceCollection|null $baseNamespaces = null,
    ): PlaceholderReplacementCollection
    {
        preg_match_all("(~[A-Za-z1-9\\\]+:[A-Za-z1-9]+)", $query, $matches);

        if (count($matches) == 0) {
            return new PlaceholderReplacementCollection();
        }

        $getQueryReplacement = function (IdentifierInterface $replacement, OverrideCollection|null $overrides = null): string {
            if ($overrides !== null && $overrides->keyExists($replacement)) {
                return $overrides->getValue($replacement);
            }

            return $replacement instanceof BackedEnum ? $replacement->value : strtolower($replacement->name);
        };

        $placeholders = array_map(fn($item) => [$item, explode(':', substr($item, 1))], $matches[0]);
        $placeholderReplacements = [];

        foreach ($placeholders as $item) {
            [$placeholder, $pair] = $item;
            if (class_exists($pair[0])) {
                $replacement = getReplacementFromStrings($pair[0], $pair[1]);
                if ($replacement instanceof IdentifierInterface) {
                    $placeholderReplacements[] = new PlaceholderReplacement($placeholder, $getQueryReplacement($replacement, $overrides));
                }
            } elseif ($baseNamespaces instanceof BaseNamespaceCollection) {
                foreach ($baseNamespaces->getAll() as $baseNamespace) {
                    $possibleClass = $baseNamespace . '\\' . $pair[0];
                    if (class_exists($possibleClass)) {
                        $replacement = getReplacementFromStrings($possibleClass, $pair[1]);
                        if ($replacement instanceof IdentifierInterface) {
                            $placeholderReplacements[] = new PlaceholderReplacement($placeholder, $getQueryReplacement($replacement, $overrides));
                        }
                        // Additional classes will never be replaced
                        break;
                    }
                }
            }
        }

        return new PlaceholderReplacementCollection(...$placeholderReplacements);
    }

    function replacePlaceholders(
        string                       $query,
        OverrideCollection|null      $overrides = null,
        BaseNamespaceCollection|null $baseNamespaces = null,
    ): string
    {
        $placeholderReplacements = collectPlaceholders($query, $overrides, $baseNamespaces);

        return str_replace($placeholderReplacements->getPlaceholders(), $placeholderReplacements->getReplacements(), $query);
    }

    function collectQueryParameters(string $query, QueryParametersCollection $queryParameters, BaseNamespaceCollection|null $baseNamespaces = null): array
    {
        preg_match_all("(:[A-Za-z1-9\\\]+:[A-Za-z1-9]+)", $query, $matches);

        if (count($matches) == 0) {
            return [];
        }

        $placeholders = array_map(fn($item) => [$item, explode(':', substr($item, 1))], $matches[0]);
        $placeholderReplacements = [];

        foreach ($placeholders as $item) {
            [$placeholder, $pair] = $item;

            if ($pair[0] == 'Array') {
                $value = $queryParameters->getArrayValue($placeholder);
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $placeholderReplacements[$placeholder . '_' . $k] = $v;
                    }
                }
            }

            if (class_exists($pair[0])) {
                $replacement = getReplacementFromStrings($pair[0], $pair[1]);
                if ($replacement instanceof IdentifierInterface && $queryParameters->keyExists($replacement)) {
                    $placeholderReplacements[$placeholder] = $queryParameters->getValue($replacement);
                }
            } elseif ($baseNamespaces instanceof BaseNamespaceCollection) {
                foreach ($baseNamespaces->getAll() as $baseNamespace) {
                    $possibleClass = $baseNamespace . '\\' . $pair[0];
                    if (class_exists($possibleClass)) {
                        $replacement = getReplacementFromStrings($possibleClass, $pair[1]);
                        if ($replacement instanceof IdentifierInterface && $queryParameters->keyExists($replacement)) {
                            $placeholderReplacements[$placeholder] = $queryParameters->getValue($replacement);
                            // No need to continue the loop once the class is found
                            break;
                        }
                    }
                }
            }
        }

        return $placeholderReplacements;
    }

    function createMapFromArray(IdentifierInterface $replacement, array $data): SplObjectStorage
    {
        $cases = $replacement::cases();
        $map  = new SplObjectStorage();

        foreach ($data as $name => $value) {
            foreach ($cases as $case) {
                $match = $case instanceof BackedEnum ? $case->value : strtolower($case->name);
                if($name === $match) {
                    $map[$case] = $value;
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

    function getStatement(Connection $conn, string $query, QueryParametersCollection|null $parameters = null, OverrideCollection|null $overrides = null, BaseNamespaceCollection|null $baseNamespaces = null): PDOStatement
    {
        $query = replacePlaceholders($query, $overrides, $baseNamespaces);
        $statementParameters = [];

        if ($parameters !== null) {
            $statementParameters = collectQueryParameters($query, $parameters, $baseNamespaces);
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