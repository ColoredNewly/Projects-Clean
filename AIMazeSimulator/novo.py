import pygame
import sys
from collections import deque
import heapq

# ---------------- SETTINGS ----------------
WIDTH, HEIGHT = 900, 600
ROWS, COLS = 30, 30
CELL_SIZE = 20
PANEL_X = COLS * CELL_SIZE

# Colors
WHITE = (255, 255, 255)
BLACK = (0, 0, 0)
GREY = (200, 200, 200)

GREEN = (0, 255, 0)
RED = (255, 0, 0)

BLUE = (0, 150, 255)
YELLOW = (255, 255, 0)

DELAY = 10

pygame.init()
WIN = pygame.display.set_mode((WIDTH, HEIGHT))
pygame.display.set_caption("AI Pathfinding System")
FONT = pygame.font.SysFont("Arial", 20)

# ---------------- GRID ----------------
grid = [[0 for _ in range(COLS)] for _ in range(ROWS)]
start = None
end = None
visited = set()
parent = {}
path = set()

# ---------------- BUTTON ----------------
class Button:
    def __init__(self, x, y, w, h, text, action):
        self.rect = pygame.Rect(x, y, w, h)
        self.text = text
        self.action = action

    def draw(self):
        pygame.draw.rect(WIN, GREY, self.rect)
        label = FONT.render(self.text, True, (0, 0, 0))
        WIN.blit(label, (self.rect.x + 10, self.rect.y + 10))

    def click(self, pos):
        if self.rect.collidepoint(pos):
            self.action()

# ---------------- DRAW ----------------
def draw():
    for i in range(ROWS):
        for j in range(COLS):
            color = WHITE

            if grid[i][j] == 1:
                color = BLACK

            if (i, j) in visited:
                color = BLUE

            if (i, j) in path:
                color = YELLOW

            if (i, j) == start:
                color = GREEN

            if (i, j) == end:
                color = RED

            pygame.draw.rect(WIN, color,
                (j * CELL_SIZE, i * CELL_SIZE, CELL_SIZE, CELL_SIZE))

            pygame.draw.rect(WIN, GREY,
                (j * CELL_SIZE, i * CELL_SIZE, CELL_SIZE, CELL_SIZE), 1)

# ---------------- HELPERS ----------------
def get_pos():
    x, y = pygame.mouse.get_pos()

    if x >= PANEL_X:
        return None

    row = y // CELL_SIZE
    col = x // CELL_SIZE

    if row >= ROWS or col >= COLS:
        return None

    return (row, col)

def reset_search():
    global visited, parent, path
    visited = set()
    parent = {}
    path = set()

def reconstruct():
    global path
    path = set()

    node = end
    while node in parent:
        node = parent[node]
        if node != start:
            path.add(node)

# ---------------- BFS ----------------
def bfs():
    global visited, parent

    if not start or not end:
        return

    reset_search()

    q = deque([start])
    visited.add(start)

    while q:
        pygame.time.delay(DELAY)

        current = q.popleft()

        if current == end:
            break

        r, c = current

        for dr, dc in [(1,0),(-1,0),(0,1),(0,-1)]:
            nr, nc = r + dr, c + dc

            if 0 <= nr < ROWS and 0 <= nc < COLS:
                if grid[nr][nc] == 0 and (nr, nc) not in visited:
                    q.append((nr, nc))
                    visited.add((nr, nc))
                    parent[(nr, nc)] = (r, c)

        WIN.fill(WHITE)
        draw()
        draw_buttons()
        pygame.display.update()

    reconstruct()

    # 🔥 THIS IS THE FIX (FORCE FINAL DRAW)
    WIN.fill(WHITE)
    draw()
    draw_buttons()
    pygame.display.update()

# ---------------- DFS ----------------
def dfs():
    global visited, parent

    if not start or not end:
        return

    reset_search()

    stack = [start]
    visited.add(start)

    while stack:
        pygame.time.delay(DELAY)

        current = stack.pop()

        if current == end:
            break

        r, c = current

        for dr, dc in [(1,0),(-1,0),(0,1),(0,-1)]:
            nr, nc = r + dr, c + dc

            if 0 <= nr < ROWS and 0 <= nc < COLS:
                if grid[nr][nc] == 0 and (nr, nc) not in visited:
                    stack.append((nr, nc))
                    visited.add((nr, nc))
                    parent[(nr, nc)] = (r, c)

        WIN.fill(WHITE)
        draw()
        draw_buttons()
        pygame.display.update()

    reconstruct()

    WIN.fill(WHITE)
    draw()
    draw_buttons()
    pygame.display.update()

# ---------------- A* ----------------
def heuristic(a, b):
    return abs(a[0] - b[0]) + abs(a[1] - b[1])

def astar():
    global visited, parent

    if not start or not end:
        return

    reset_search()

    open_set = []
    heapq.heappush(open_set, (0, start))
    g = {start: 0}

    while open_set:
        pygame.time.delay(DELAY)

        _, current = heapq.heappop(open_set)

        if current == end:
            break

        visited.add(current)

        r, c = current

        for dr, dc in [(1,0),(-1,0),(0,1),(0,-1)]:
            nr, nc = r + dr, c + dc

            if 0 <= nr < ROWS and 0 <= nc < COLS:
                if grid[nr][nc] == 1:
                    continue

                temp = g[current] + 1

                if (nr, nc) not in g or temp < g[(nr, nc)]:
                    g[(nr, nc)] = temp
                    f = temp + heuristic((nr, nc), end)

                    heapq.heappush(open_set, (f, (nr, nc)))
                    parent[(nr, nc)] = current

        WIN.fill(WHITE)
        draw()
        draw_buttons()
        pygame.display.update()

    reconstruct()

    WIN.fill(WHITE)
    draw()
    draw_buttons()
    pygame.display.update()

# ---------------- BUTTONS ----------------
buttons = [
    Button(650, 50, 200, 40, "Run BFS", bfs),
    Button(650, 110, 200, 40, "Run DFS", dfs),
    Button(650, 170, 200, 40, "Run A*", astar),
    Button(650, 230, 200, 40, "Reset", lambda: reset_all()),
    Button(650, 290, 200, 40, "Clear Path", reset_search)
]

def draw_buttons():
    for b in buttons:
        b.draw()

def reset_all():
    global grid, start, end
    reset_search()
    grid = [[0 for _ in range(COLS)] for _ in range(ROWS)]
    start = None
    end = None

# ---------------- MAIN ----------------
def main():
    global start, end

    while True:
        WIN.fill(WHITE)
        draw()
        draw_buttons()

        mouse = pygame.mouse.get_pressed()
        pos = get_pos()

        if pos:
            if mouse[0]:
                if pos != start and pos != end:
                    grid[pos[0]][pos[1]] = 1
            if mouse[2]:
                grid[pos[0]][pos[1]] = 0

        for event in pygame.event.get():

            if event.type == pygame.QUIT:
                pygame.quit()
                sys.exit()

            if event.type == pygame.MOUSEBUTTONDOWN:
                for b in buttons:
                    b.click(pygame.mouse.get_pos())

            if event.type == pygame.KEYDOWN:
                if event.key == pygame.K_s:
                    start = get_pos()
                if event.key == pygame.K_e:
                    end = get_pos()

        pygame.display.update()

main()