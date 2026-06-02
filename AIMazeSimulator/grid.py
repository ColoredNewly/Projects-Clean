import pygame
from settings import *

class Grid:
    def __init__(self):
        self.grid = [[0 for _ in range(COLS)] for _ in range(ROWS)]

        self.start = None
        self.end = None

    def reset_search(self):
        self.visited = set()
        self.parent = {}

    def draw(self, win, visited=set()):
        for i in range(ROWS):
            for j in range(COLS):

                color = WHITE

                if self.grid[i][j] == 1:
                    color = BLACK

                if (i, j) in visited:
                    color = BLUE

                if self.start == (i, j):
                    color = GREEN

                if self.end == (i, j):
                    color = RED

                pygame.draw.rect(
                    win,
                    color,
                    (j * CELL_SIZE, i * CELL_SIZE, CELL_SIZE, CELL_SIZE)
                )

                pygame.draw.rect(
                    win,
                    GREY,
                    (j * CELL_SIZE, i * CELL_SIZE, CELL_SIZE, CELL_SIZE),
                    1
                )

    def get_pos(self):
        x, y = pygame.mouse.get_pos()
        return y // CELL_SIZE, x // CELL_SIZE

    def handle_mouse(self):

        mouse = pygame.mouse.get_pressed()
        x, y = pygame.mouse.get_pos()

        if x >= COLS * CELL_SIZE:
            return

        col = x // CELL_SIZE
        row = y // CELL_SIZE

        if row >= ROWS or col >= COLS:
            return

        if mouse[0]:
            if (row, col) != self.start and (row, col) != self.end:
                self.grid[row][col] = 1

        if mouse[2]:
            self.grid[row][col] = 0