import pygame
from settings import *

def bfs(grid_obj, win):
    from collections import deque

    start = grid_obj.start
    end = grid_obj.end

    visited = set()
    parent = {}

    q = deque()
    q.append(start)
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
                if grid_obj.grid[nr][nc] == 0 and (nr, nc) not in visited:
                    q.append((nr, nc))
                    visited.add((nr, nc))
                    parent[(nr, nc)] = (r, c)

        win.fill(WHITE)
        grid_obj.draw(win, visited)
        pygame.display.update()

    # reconstruct path
    node = end
    while node in parent:
        pygame.time.delay(DELAY)
        node = parent[node]
        if node != start:
            r, c = node
            pygame.draw.rect(win, YELLOW,
                (c * CELL_SIZE, r * CELL_SIZE, CELL_SIZE, CELL_SIZE))
            pygame.display.update()