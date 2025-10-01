# Deploying on Coolify

This guide explains how to deploy your Symfony application on [Coolify](https://coolify.io/).

## Prerequisites

- A Coolify instance (self-hosted or cloud)
- Your application code in a Git repository
- A domain name (optional but recommended for HTTPS)

## Quick Start

1. **Create a New Project in Coolify**
   - Navigate to your Coolify dashboard
   - Create a new project from your Git repository

2. **⚠️ IMPORTANT: Configure Docker Compose File**

   In Coolify's project settings, you MUST explicitly set the Docker Compose file:

   - Go to your project's configuration/settings
   - Look for "Docker Compose Location" or "Compose File Path" setting
   - Set it to: `compose.coolify.yaml`
   - Save the settings

   **Do not skip this step!** If not set, Coolify will use `compose.yaml` which has incompatible syntax.

3. **Set Environment Variables**

   In Coolify's Environment Variables section, add the following required variables:

   ```bash
   # Generate secure secrets with: openssl rand -hex 32
   SERVER_NAME=your-domain.com
   APP_SECRET=your-secure-random-string-here
   CADDY_MERCURE_JWT_SECRET=your-secure-random-string-here

   # Database credentials
   POSTGRES_DB=tablesoccer
   POSTGRES_USER=tablesoccer_user
   POSTGRES_PASSWORD=your-secure-database-password
   ```

   See `.env.coolify.example` for a complete list with descriptions.

4. **Deploy**
   - Click "Deploy" in Coolify
   - Wait for the build and deployment to complete
   - Your app will be available at your configured domain

## Environment Variables

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `SERVER_NAME` | Your domain name | `tablesoccer.example.com` |
| `APP_SECRET` | Symfony application secret | Generate with `openssl rand -hex 32` |
| `CADDY_MERCURE_JWT_SECRET` | Mercure JWT secret | Generate with `openssl rand -hex 32` |
| `POSTGRES_DB` | Database name | `tablesoccer` |
| `POSTGRES_USER` | Database user | `tablesoccer_user` |
| `POSTGRES_PASSWORD` | Database password | Strong password |

### Optional Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `POSTGRES_VERSION` | PostgreSQL version | `16` |
| `HTTP_PORT` | HTTP port | `80` |
| `HTTPS_PORT` | HTTPS port | `443` |
| `HTTP3_PORT` | HTTP/3 port | `443` |

## SSL/TLS Certificates

Coolify handles SSL/TLS certificates automatically using Let's Encrypt when you set a proper `SERVER_NAME` domain.

Ensure your domain's DNS A record points to your Coolify server's IP address.

## Troubleshooting

### ❌ Build Fails with "Invalid template" Error (MOST COMMON ISSUE)

If you see an error like:
```
failed to read /artifacts/.env: Invalid template: "https://${SERVER_NAME:-localhost"
exit status 1
```

**This is the #1 most common issue.** It means Coolify is reading the wrong compose file.

**Solution:**

1. **Verify Compose File Setting in Coolify:**
   - Go to your project settings in Coolify
   - Find the "Docker Compose Location" or "Compose File" field
   - Ensure it's set to: `compose.coolify.yaml` (not `compose.yaml` or `compose.prod.yaml`)
   - **Save the settings**

2. **Verify Environment Variables are Set:**
   - Go to Environment Variables section in Coolify
   - Ensure all required variables from `.env.coolify.example` are set
   - Especially: `SERVER_NAME`, `APP_SECRET`, `CADDY_MERCURE_JWT_SECRET`, database credentials

3. **Redeploy:**
   - After saving the compose file setting and environment variables
   - Trigger a new deployment

**Why this happens:**
- The default `compose.yaml` uses bash-style variable substitution like `${VAR:-default}`
- Coolify's template engine doesn't support this syntax
- The `compose.coolify.yaml` file uses simple `${VAR}` syntax that Coolify can parse

### Database Connection Issues

Verify that:
1. `DATABASE_URL` environment variables match your Coolify settings
2. The database service is running (check Coolify logs)
3. The database health check is passing

### Application Not Starting

Check the logs in Coolify:
1. Go to your project > Deployments > View Logs
2. Look for PHP/Symfony errors
3. Ensure all required environment variables are set

## Differences from Local Development

The `compose.coolify.yaml` file is optimized for production and differs from the local development setup:

- Uses the `frankenphp_prod` build target (optimized, no dev dependencies)
- Requires explicit environment variables (no defaults)
- Designed for Coolify's template system
- No development tools or debug mode

## Additional Resources

- [Coolify Documentation](https://coolify.io/docs)
- [Symfony Docker Documentation](../README.md)
- [Production Deployment Guide](./production.md)
