import pygame
pygame.init()

WIDTH = 700
HEIGHT = 500

WIN = pygame.display.set_mode((WIDTH, HEIGHT))
pygame.display.set_caption("Pong$ine")

BLACK = (0, 0, 0)
WHITE = (255, 255, 255)

PADDLE_WIDTH = 20
PADDLE_HEIGHT = 100

FPS = 60

font = pygame.font.SysFont("comicsans", 50)


class Paddle():
    COLOR = WHITE
    VEL = 4

    def __init__(self, x, y, width, height):
        self.x = x
        self.y = y
        self.width = width
        self.height = height

    def draw(self, win):
        pygame.draw.rect(win, self.COLOR, (self.x, self.y, self.width, self.height))

    def move(self, up=True):
        if up:
            self.y -= self.VEL
        else:
            self.y += self.VEL

class Ball():
    MAX_VEL = 5
    COLOR = WHITE

    def __init__(self, x, y, radius):
        self.x = self.original_x = x
        self.y = self.original_y = y
        self.radius = radius
        self.x_vel = self.MAX_VEL
        self.y_vel = 0

    def draw(self, win):
        pygame.draw.circle(win, self.COLOR, (self.x, self.y), self.radius)

    def move(self):
        self.x += self.x_vel
        self.y += self.y_vel

    def reset(self):
        self.x = self.original_x
        self.y = self.original_y
        self.y_vel = 0
        self.x_vel *= -1





def main():
    run = True
    clock = pygame.time.Clock()

    left_paddle = Paddle(10, HEIGHT // 2 - PADDLE_HEIGHT//2, PADDLE_WIDTH, PADDLE_HEIGHT)
    right_paddle = Paddle(WIDTH - 10 - PADDLE_WIDTH, HEIGHT//2-PADDLE_HEIGHT//2, PADDLE_WIDTH, PADDLE_HEIGHT)
    ball = Ball(WIDTH//2, HEIGHT//2, 7)

    left_score = 0
    right_score = 0



    while run:
        clock.tick(FPS)

        WIN.fill(BLACK)

        left_score_text = font.render(f"{left_score}", 1, WHITE)
        right_score_text = font.render(f"{right_score}", 1, WHITE)
        WIN.blit(left_score_text, (WIDTH//4 - left_score_text.get_width()//2, 20))
        WIN.blit(right_score_text, (WIDTH * (3/4) - right_score_text.get_width()//2, 20))
        left_paddle.draw(WIN)
        right_paddle.draw(WIN)
        ball.draw(WIN)


        pygame.display.update()

        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                run = False

        keys = pygame.key.get_pressed()

        if keys[pygame.K_UP] and right_paddle.y >= 0:
            right_paddle.move(up=True)
        if keys[pygame.K_DOWN] and right_paddle.y + PADDLE_HEIGHT <= HEIGHT:
            right_paddle.move(up=False)
        if keys[pygame.K_w] and left_paddle.y >= 0:
            left_paddle.move(up=True)
        if keys[pygame.K_s] and left_paddle.y + PADDLE_HEIGHT <= HEIGHT:
            left_paddle.move(up=False)

        ball.move()

        if ball.x_vel < 0:# x.vel e 5 koa topkata ode desno. Koa topkata go dopre densio paddle, x.vel se se pomnoze so -1
            if ball.y >= left_paddle.y and ball.y <= left_paddle.y + left_paddle.height:
                if ball.x - ball.radius <= left_paddle.x + left_paddle.width:
                    ball.x_vel *= -1

                    middle_y = left_paddle.y + left_paddle.height / 2
                    difference_in_y = middle_y - ball.y
                    reduction_factor = 10#(left_paddle.height / 2) / ball.MAX_VEL
                    y_vel = difference_in_y / reduction_factor#5/10/15/20/25...50
                    ball.y_vel = -1 * y_vel

        else:
            if ball.y >= right_paddle.y and ball.y <= right_paddle.y + right_paddle.height:
                if ball.x + ball.radius >= right_paddle.x:
                    ball.x_vel *= -1

                    middle_y = right_paddle.y + right_paddle.height / 2
                    difference_in_y = middle_y - ball.y
                    reduction_factor = (left_paddle.height / 2) / ball.MAX_VEL
                    y_vel = difference_in_y / reduction_factor
                    ball.y_vel = -1 * y_vel

        if ball.y + ball.radius >= HEIGHT:
            ball.y_vel *= -1
        if ball.y - ball.radius <= 0:
            ball.y_vel *= -1

        if ball.x < 0:
            right_score += 1
            ball.reset()
        elif ball.x > WIDTH:
            left_score += 1
            ball.reset()


if __name__ == '__main__':
    main()






