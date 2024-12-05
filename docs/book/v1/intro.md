# Introduction

This library provides two adapters for [laminas/laminas-paginator](https://docs.laminas.dev/laminas-paginator):

- `Laminas\Paginator\Adapter\LaminasDb\DbSelect`
- `Laminas\Paginator\Adapter\LaminasDb\DbTableGateway`

These provide pagination support for [laminas/laminas-db](https://docs.laminas.dev/laminas-db/) SQL select and TableGateway features.

- [DbSelect documentation](db-select.md)
- [DbTableGateway documentation](db-table-gateway.md)

Each is configured with the `Laminas\Paginator\AdapterPluginManager` when used in laminas-mvc applications, or in applications utilizing config providers, such as Mezzio applications.
