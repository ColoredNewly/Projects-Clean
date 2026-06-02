import pygame
from settings import *

def dfs(grid_obj, win):

    start = grid_obj.start
    end = grid_obj.end

    visited = set()
    parent = {}

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
                if grid_obj.grid[nr][nc] == 0 and (nr, nc) not in visited:
                    stack.append((nr, nc))
                    visited.add((nr, nc))
                    parent[(nr, nc)] = (r, c)

        win.fill(WHITE)
        grid_obj.draw(win, visited)
        pygame.display.update()

    node = end
    while node in parent:
        pygame.time.delay(DELAY)
        node = parent[node]
        if node != start:
            r, c = node
            pygame.draw.rect(win, YELLOW,
                (c * CELL_SIZE, r * CELL_SIZE, CELL_SIZE, CELL_SIZE))
            pygame.display.update()