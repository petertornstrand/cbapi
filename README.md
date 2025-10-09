# Codebase API Gateway

This repository contains an API Gateway service, which acts as an intermediary
between the client and the Codebase platform. The service decorates the
responses from the Codebase API platform with additional metadata and changes
the response format from XML to JSON.

To use this service, you need a [Codebase](https://www.codebasehq.com/) account. 

Se the included [API documentation](https://github.com/petertornstrand/cbapi/blob/main/cbapi.openapi.json) for more information.

## Transformers

The API Gateway service includes transformers that are used to modify the
responses from the Codebase API platform. These transformers mainly change the
response format from XML to JSON.

## Decorators

The API Gateway service includes decorators that can be used to modify the
responses from the Codebase API platform. These decorators can be used to add
additional data to the responses.

Decorators are implemented as plugins and the decorations are loaded from the
`var/storage/*.decorator.json` files.
