# SaveMyPlant

A plant doctor for home gardeners, built with [Symfony AI](https://ai.symfony.com) and the official [PHP MCP SDK](https://github.com/modelcontextprotocol/php-sdk).

> Work in progress for the Symfony AI & MCP Hackathon (Oct 26 – Nov 22, 2026).

## MCP tools

| Tool | Status | What it does |
|---|---|---|
| `disease_risk` | ✅ | Late blight (mildiou) risk for tomatoes and potatoes over the coming days, from the Open-Meteo forecast (Smith Period rule) |
| `diagnose_plant` | 🚧 | Identify a disease from a leaf photo (vision model) |
| `sowing_calendar` | 🚧 | What to sow this week, for a given city |
| `companion_plants` | 🚧 | Good and bad neighbours for a crop |

## Requirements

- PHP 8.2+
- Composer 2

## Run it

```bash
git clone https://github.com/meddrh/savemyplant.git
cd savemyplant
composer install
php vendor/bin/phpunit
```

### From an MCP client (STDIO)

Add the server to your client, e.g. Claude Desktop (`claude_desktop_config.json`):

```json
{
    "mcpServers": {
        "savemyplant": {
            "command": "php",
            "args": ["/absolute/path/to/savemyplant/bin/console", "mcp:server"]
        }
    }
}
```

Then ask: *"Is there a late blight risk for my tomatoes in Lyon this week?"*

### Over HTTP

```bash
symfony serve
```

The MCP endpoint is `http://localhost:8000/mcp`.

## License

MIT
