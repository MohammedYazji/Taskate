# Taskate

A clean, minimal task management app built with Laravel. Organize your work into projects, sections, and tasks, with AI-powered task generation, a Pomodoro timer, habit tracking, and more.

## Features

### Task Management
- Create, edit, and organize tasks inside projects
- Sections to group tasks within a project (like folders within a folder)
- Subtasks with completion tracking
- Priority levels (low, medium, high) and due dates
- "Won't Do" status for skipping tasks without deleting them
- Quick-add tasks directly from the topbar search input
- Drag and drop to reorder tasks and move them between sections

### Project Views
- **List view** with clean task lists and collapsible sections
- **Board view** with kanban-style columns organized by section
- **Group by** section, status, priority, or view them flat
- **Sort by** manual order, priority, due date, or alphabetically
- Hide completed tasks with a single click

### AI Task Generation
- Describe a topic in plain language and get a full project plan back
- AI generates sections, tasks with descriptions, subtasks, and due dates
- Review and edit everything before approving
- Choose which folder to save the generated project into

### Smart Views
- **Today** for tasks due today across all projects
- **Next 7 Days** for the upcoming week at a glance
- **Inbox** for unassigned tasks
- **Completed** to browse done/won't-do tasks filtered by date range and project

### Organization
- Folders to group related projects
- Tags with custom colors and icons
- Parent-child tag relationships
- Drag and drop to reorder projects and move them between folders

### Pomodoro Timer
- 25/5 focus/break intervals
- Track your focus sessions over time

### Habit Tracker
- Create habits and check them off daily
- Streak tracking and completion stats
- Archive old habits without deleting them

### Google OAuth
- Sign in with your Google account, no password needed

## Tech Stack

- **Laravel + Inertia** for the backend and page-driven frontend
- **React** for the UI components
- **Phosphor Icons** for clean, consistent icons
- **Tiptap** for the rich text editor in task descriptions
- **SortableJS** for drag and drop
- **Tailwind CSS** for styling
- **Google Gemini API** for AI task generation
- **Laravel Socialite** for Google OAuth

## Getting Started

```bash
git clone <repo-url>
cd Taskate
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Set up Google OAuth credentials in `.env` if you want Google sign-in:

```
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8001/auth/google/callback
```

For AI task generation, add your Gemini API key:

```
GEMINI_API_KEY=your-api-key
```

The app runs on `http://localhost:8001`.
