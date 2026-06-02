import pygame
import heapq
from settings import *

def heuristic(a, b):
    return abs(a[0] - b[0]) + abs(a[1] - b[1])


def astar(grid_obj, win):

    start = grid_obj.start
    end = grid_obj.end

    visited = set()
    parent = {}

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
                if grid_obj.grid[nr][nc] == 1:
                    continue

                temp_g = g[current] + 1

                if (nr, nc) not in g or temp_g < g[(nr, nc)]:
                    g[(nr, nc)] = temp_g
                    f = temp_g + heuristic((nr, nc), end)

                    heapq.heappush(open_set, (f, (nr, nc)))
                    parent[(nr, nc)] = current

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