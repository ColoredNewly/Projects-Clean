import pygame

class Button:
    def __init__(self, x, y, w, h, text, color, action):
        self.rect = pygame.Rect(x, y, w, h)
        self.text = text
        self.color = color
        self.action = action

    def draw(self, win, font):
        pygame.draw.rect(win, self.color, self.rect)
        label = font.render(self.text, True, (0, 0, 0))
        win.blit(label, (self.rect.x + 10, self.rect.y + 10))

    def click(self, pos):
        if self.rect.collidepoint(pos):
            self.action()